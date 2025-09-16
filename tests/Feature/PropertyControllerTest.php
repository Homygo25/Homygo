<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Property;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertyControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $category;
    protected $location;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test categories and locations
        $this->category = Category::create([
            'name' => 'Houses',
            'slug' => 'houses',
            'description' => 'Residential houses'
        ]);
        
        $this->location = Location::create([
            'name' => 'New York',
            'slug' => 'new-york',
            'type' => 'city'
        ]);
    }

    public function test_guest_can_view_properties_index()
    {
        $response = $this->get('/properties');
        $response->assertStatus(200);
    }

    public function test_guest_can_view_single_property()
    {
        $user = User::factory()->create();
        $property = Property::create([
            'title' => 'Test Property',
            'slug' => 'test-property',
            'description' => 'A beautiful property',
            'address' => '123 Test Street',
            'city' => 'New York',
            'state' => 'NY',
            'zip_code' => '10001',
            'price' => 500000,
            'property_type' => 'house',
            'status' => 'active',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'area' => 1500,
            'user_id' => $user->id,
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
        ]);

        $response = $this->get("/properties/{$property->id}");
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_create_property()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->post('/properties', [
            'title' => 'New Property',
            'description' => 'A brand new property',
            'type' => 'sale',
            'address' => '456 New Street',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'postal_code' => '90210',
            'price' => 750000,
            'property_type' => 'apartment',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'area' => 1200,
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'images' => [
                UploadedFile::fake()->image('property1.jpg', 800, 600),
                UploadedFile::fake()->image('property2.jpg', 800, 600),
            ]
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('properties', [
            'title' => 'New Property',
            'user_id' => $user->id
        ]);
    }

    public function test_home_page_loads()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
