<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Property;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use function view;
use function redirect;
use function route;
use function back;
use function __;
use function event;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        // Validate with both name formats - either combined name or first/last separately
        if ($request->filled('name')) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phone' => 'nullable|string|max:20',
                'password' => ['required', 'confirmed', PasswordRule::min(8)],
                'role' => 'nullable|in:buyer,seller,agent,contractor',
                'terms' => 'sometimes|accepted',
            ]);

            // Parse name into first and last
            $nameParts = explode(' ', $request->input('name'), 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';
            $userName = $request->input('name');
        } else {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phone' => 'nullable|string|max:20',
                'password' => ['required', 'confirmed', PasswordRule::min(8)],
                'role' => 'required|in:buyer,seller,agent,contractor',
                'terms' => 'required|accepted',
            ]);

            $firstName = $request->input('first_name');
            $lastName = $request->input('last_name');
            $userName = $firstName . ' ' . $lastName;
        }

        // Create user
        $user = User::create([
            'name' => $userName,
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        // Create user profile
        $user->userProfile()->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $request->input('phone'),
            'role' => $request->input('role') ?? 'buyer',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Account created successfully! Welcome to Finder.');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show password reset form
     */
    public function showPasswordReset()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendPasswordResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show user dashboard
     */
    public function dashboard()
    {
        /** @var User $user */
        $user = Auth::user();
        $userProfile = $user->profile ?? $user->userProfile;

        // Get user's properties
        $properties = $user->properties()
            ->with(['category', 'location'])
            ->latest()
            ->paginate(10);

        // Get statistics
        $stats = [
            'total_properties' => $user->properties()->count(),
            'active_properties' => $user->properties()->where('status', 'active')->count(),
            'featured_properties' => $user->properties()->where('is_featured', true)->count(),
            'total_views' => $user->properties()->sum('views'),
        ];

        return view('account-listings', compact('user', 'userProfile', 'properties', 'stats'));
    }

    /**
     * Show user profile
     */
    public function profile()
    {
        $user = Auth::user();
        $userProfile = $user->userProfile;

        return view('account-profile', compact('user', 'userProfile'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $userProfile = $user->userProfile;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'company_name' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|max:1024', // Max 1MB
        ]);

        // Update user
        $emailChanged = $user->email !== $request->email;

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($emailChanged) {
            $data['email_verified_at'] = null;
        }

        $user->update($data);

        // Handle avatar upload
        $avatarPath = $userProfile ? $userProfile->avatar : null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')
                ->storeAs('avatars', 'user_' . $user->id . '.' . $request->file('avatar')->getClientOriginalExtension(), 'public');
        }

        // Parse name into first and last name for profile
    $nameParts = explode(' ', $request->input('name'), 2);
    $firstName = $nameParts[0];
    $lastName = $nameParts[1] ?? '';

        // Update or create profile
        if ($userProfile) {
            $userProfile->update([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $request->input('phone'),
                'bio' => $request->input('bio'),
                'company_name' => $request->input('company_name'),
                'website' => $request->input('website'),
                'address' => $request->input('address'),
                'avatar' => $avatarPath,
            ]);
        } else {
            $user->userProfile()->create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $request->input('phone'),
                'bio' => $request->input('bio'),
                'company_name' => $request->input('company_name'),
                'website' => $request->input('website'),
                'address' => $request->input('address'),
                'avatar' => $avatarPath,
            ]);
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Show user's listings
     */
    public function listings()
    {
        /** @var User $user */
        $user = Auth::user();

        $properties = $user->properties()
            ->with(['category', 'primaryImage'])
            ->latest()
            ->paginate(10);

        return view('account-listings', compact('properties'));
    }

    /**
     * Show user's favorites
     */
    public function favorites()
    {
        /** @var User $user */
        $user = Auth::user();

        $favorites = $user->favorites()
            ->with(['favoritable'])
            ->latest()
            ->paginate(10);

        return view('account-favorites', compact('favorites'));
    }

    /**
     * Show account settings
     */
    public function settings()
    {
        $user = Auth::user();

        return view('account-settings', compact('user'));
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match your current password.'], 'updatePassword');
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Password updated successfully!');
    }

    /**
     * Show email verification form
     */
    public function showVerifyEmailForm(Request $request)
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(route('dashboard'))
            : view('auth.verify-email');
    }

    /**
     * Handle email verification
     */
    public function verifyEmail(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->intended(route('dashboard') . '?verified=1')
            ->with('success', 'Email verified successfully!');
    }

    /**
     * Resend email verification
     */
    public function resendVerification(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }

    /**
     * Show password confirmation form
     */
    public function showConfirmPasswordForm()
    {
        return view('auth.confirm-password');
    }

    /**
     * Handle password confirmation
     */
    public function confirmPassword(Request $request): RedirectResponse
    {
        if (!Hash::check($request->password, $request->user()->password)) {
            return back()->withErrors([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Show password reset form
     */
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', ['request' => $request, 'token' => $token]);
    }

    /**
     * Handle password reset
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    /**
     * Delete user profile
     */
    public function deleteProfile(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
