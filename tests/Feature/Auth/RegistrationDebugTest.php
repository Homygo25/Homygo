<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_debug(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Debug response
        echo "Response status: " . $response->status() . "\n";
        echo "Redirect location: " . $response->headers->get('location') . "\n";
        
        // Check session for errors
        $session = $response->getSession();
        if ($session && $session->has('errors')) {
            echo "Validation errors: " . json_encode($session->get('errors')->toArray()) . "\n";
        }
        
        // Check if user was created
        $user = User::where('email', 'test@example.com')->first();
        if ($user === null) {
            echo "User was not created\n";
        } else {
            echo "User created successfully\n";
        }
        
        $this->assertTrue(true); // Always pass for debugging
    }
}
