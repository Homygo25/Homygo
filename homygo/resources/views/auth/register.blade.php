<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <label for="name">Name</label>
            <input id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" value="{{ old('name') }}">
            @error('name')
                <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <label for="email">Email</label>
            <input id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" value="{{ old('email') }}">
            @error('email')
                <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror
        </div>

        <!-- Role Selection -->
        <div class="mt-4">
            <label for="role">Register as:</label>
            <select name="role" id="role" required class="block mt-1 w-full">
                <option value="renter" {{ old('role') == 'renter' ? 'selected' : '' }}>Renter</option>
                <option value="landlord" {{ old('role') == 'landlord' ? 'selected' : '' }}>Landlord</option>
            </select>
            @error('role')
                <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="password">Password</label>
            <input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password">
            @error('password')
                <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <label for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password">
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                Already registered?
            </a>

            <button type="submit" class="ml-4">
                Register
            </button>
        </div>
    </form>
</x-guest-layout>
