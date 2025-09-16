<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Property;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use App\Models\UserProfile;

class SamplePropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a sample user if none exists
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);

            UserProfile::create([
                'user_id' => $user->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '+1 (555) 123-4567',
                'role' => 'agent',
                'company_name' => 'Dream Properties Inc.',
            ]);
        }

        // Create sample categories if none exist
        $categories = [
            ['name' => 'Apartments', 'slug' => 'apartments', 'type' => 'property'],
            ['name' => 'Houses', 'slug' => 'houses', 'type' => 'property'],
            ['name' => 'Condos', 'slug' => 'condos', 'type' => 'property'],
            ['name' => 'Townhomes', 'slug' => 'townhomes', 'type' => 'property'],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['slug' => $categoryData['slug'], 'type' => $categoryData['type']],
                $categoryData
            );
        }

        // Create sample locations if none exist
        $locations = [
            ['name' => 'New York', 'slug' => 'new-york', 'type' => 'city'],
            ['name' => 'Los Angeles', 'slug' => 'los-angeles', 'type' => 'city'],
            ['name' => 'Chicago', 'slug' => 'chicago', 'type' => 'city'],
            ['name' => 'Miami', 'slug' => 'miami', 'type' => 'city'],
        ];

        foreach ($locations as $locationData) {
            Location::firstOrCreate(
                ['slug' => $locationData['slug'], 'type' => $locationData['type']],
                $locationData
            );
        }

        // Get categories and locations for properties
        $apartment = Category::where('slug', 'apartments')->first();
        $house = Category::where('slug', 'houses')->first();
        $condo = Category::where('slug', 'condos')->first();

        $newYork = Location::where('slug', 'new-york')->first();
        $losAngeles = Location::where('slug', 'los-angeles')->first();
        $chicago = Location::where('slug', 'chicago')->first();
        $miami = Location::where('slug', 'miami')->first();

        // Sample properties
        $properties = [
            [
                'title' => 'Luxury Downtown Apartment',
                'slug' => 'luxury-downtown-apartment',
                'description' => 'A stunning luxury apartment in the heart of downtown with panoramic city views. Features modern amenities, high-end finishes, and a private balcony.',
                'type' => 'rent',
                'property_type' => 'apartment',
                'price' => 3500.00,
                'currency' => 'USD',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'area' => 1200,
                'area_unit' => 'sqft',
                'address' => '123 Main Street, Downtown',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'features' => json_encode(['Air Conditioning', 'Balcony', 'City Views', 'Modern Kitchen']),
                'amenities' => json_encode(['Gym', 'Pool', 'Concierge', 'Rooftop Deck']),
                'status' => 'active',
                'is_featured' => true,
                'published_at' => now(),
                'user_id' => $user->id,
                'category_id' => $apartment->id,
                'location_id' => $newYork->id,
                'contact_name' => 'John Doe',
                'contact_phone' => '+1 (555) 123-4567',
                'contact_email' => 'john@example.com',
            ],
            [
                'title' => 'Spacious Family House',
                'slug' => 'spacious-family-house',
                'description' => 'Perfect family home with large backyard, modern kitchen, and quiet neighborhood. Recently renovated with premium materials throughout.',
                'type' => 'sale',
                'property_type' => 'house',
                'price' => 750000.00,
                'currency' => 'USD',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'area' => 2500,
                'area_unit' => 'sqft',
                'address' => '456 Oak Avenue, Suburb',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'postal_code' => '90210',
                'latitude' => 34.0522,
                'longitude' => -118.2437,
                'features' => json_encode(['Backyard', 'Garage', 'Fireplace', 'Walk-in Closet']),
                'amenities' => json_encode(['Swimming Pool', 'Garden', 'Patio']),
                'status' => 'active',
                'is_featured' => false,
                'published_at' => now(),
                'user_id' => $user->id,
                'category_id' => $house->id,
                'location_id' => $losAngeles->id,
                'contact_name' => 'John Doe',
                'contact_phone' => '+1 (555) 123-4567',
                'contact_email' => 'john@example.com',
            ],
            [
                'title' => 'Modern Waterfront Condo',
                'slug' => 'modern-waterfront-condo',
                'description' => 'Elegant waterfront condominium with breathtaking ocean views. Features floor-to-ceiling windows, premium appliances, and resort-style amenities.',
                'type' => 'rent',
                'property_type' => 'condo',
                'price' => 4200.00,
                'currency' => 'USD',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area' => 1800,
                'area_unit' => 'sqft',
                'address' => '789 Ocean Drive, Beachfront',
                'city' => 'Miami',
                'state' => 'FL',
                'postal_code' => '33101',
                'latitude' => 25.7617,
                'longitude' => -80.1918,
                'features' => json_encode(['Ocean Views', 'Floor-to-Ceiling Windows', 'Modern Appliances']),
                'amenities' => json_encode(['Beach Access', 'Spa', 'Valet Parking']),
                'status' => 'active',
                'is_featured' => true,
                'published_at' => now(),
                'user_id' => $user->id,
                'category_id' => $condo->id,
                'location_id' => $miami->id,
                'contact_name' => 'John Doe',
                'contact_phone' => '+1 (555) 123-4567',
                'contact_email' => 'john@example.com',
            ],
            [
                'title' => 'Cozy Studio Apartment',
                'slug' => 'cozy-studio-apartment',
                'description' => 'Charming studio apartment perfect for young professionals. Features efficient layout, modern amenities, and great location near public transport.',
                'type' => 'rent',
                'property_type' => 'apartment',
                'price' => 1800.00,
                'currency' => 'USD',
                'bedrooms' => 1,
                'bathrooms' => 1,
                'area' => 600,
                'area_unit' => 'sqft',
                'address' => '321 Park Street, Midtown',
                'city' => 'Chicago',
                'state' => 'IL',
                'postal_code' => '60601',
                'latitude' => 41.8781,
                'longitude' => -87.6298,
                'features' => json_encode(['Efficient Layout', 'Near Transit', 'Modern Kitchen']),
                'amenities' => json_encode(['Laundry', 'Fitness Center']),
                'status' => 'active',
                'is_featured' => false,
                'published_at' => now(),
                'user_id' => $user->id,
                'category_id' => $apartment->id,
                'location_id' => $chicago->id,
                'contact_name' => 'John Doe',
                'contact_phone' => '+1 (555) 123-4567',
                'contact_email' => 'john@example.com',
            ],
            [
                'title' => 'Executive Penthouse Suite',
                'slug' => 'executive-penthouse-suite',
                'description' => 'Exclusive penthouse with panoramic city views, private terrace, and luxury finishes throughout. The ultimate in urban living.',
                'type' => 'sale',
                'property_type' => 'apartment',
                'price' => 2500000.00,
                'currency' => 'USD',
                'bedrooms' => 3,
                'bathrooms' => 3,
                'area' => 3000,
                'area_unit' => 'sqft',
                'address' => '555 Skyline Boulevard, Upper East',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10028',
                'latitude' => 40.7831,
                'longitude' => -73.9712,
                'features' => json_encode(['Private Terrace', 'Panoramic Views', 'Luxury Finishes', 'Smart Home']),
                'amenities' => json_encode(['Doorman', 'Private Elevator', 'Wine Cellar']),
                'status' => 'active',
                'is_featured' => true,
                'published_at' => now(),
                'user_id' => $user->id,
                'category_id' => $apartment->id,
                'location_id' => $newYork->id,
                'contact_name' => 'John Doe',
                'contact_phone' => '+1 (555) 123-4567',
                'contact_email' => 'john@example.com',
            ],
        ];

        foreach ($properties as $propertyData) {
            Property::firstOrCreate(
                ['slug' => $propertyData['slug']],
                $propertyData
            );
        }

        $this->command->info('Sample properties created successfully!');
    }
}
