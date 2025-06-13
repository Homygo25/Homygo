<?php

namespace Tests\Feature;

use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_home(): void
    {
        $response = $this->get('/');
        $response->assertOk();
    }

    public function test_landlord_can_access_create_rental(): void
    {
        $user = User::factory()->create(['role' => 'landlord']);

        $response = $this->actingAs($user)->get('/rentals/create');
        $response->assertOk();
    }

    public function test_renter_cannot_access_create_rental(): void
    {
        $user = User::factory()->create(['role' => 'renter']);

        $response = $this->actingAs($user)->get('/rentals/create');
        $response->assertStatus(403);
    }
}
