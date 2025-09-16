<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_debug(): void
    {
        $user = User::factory()->create();
        
        echo "Original email: " . $user->email . "\n";
        echo "Original email_verified_at: " . ($user->email_verified_at ? $user->email_verified_at : 'null') . "\n";

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $user->refresh();
        
        echo "New email: " . $user->email . "\n";
        echo "New email_verified_at: " . ($user->email_verified_at ? $user->email_verified_at : 'null') . "\n";
        
        $this->assertTrue(true); // Always pass for debugging
    }
}
