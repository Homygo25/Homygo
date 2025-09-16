<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Property;
use App\Models\User;
use App\Models\Category;
use App\Models\Location;

class QuickPropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        $category = Category::first();
        $location = Location::first();

        if (!$user || !$category || !$location) {
            $this->command->error('Missing required data: users, categories, or locations');
            return;
        }

        $properties = [
            [
                'title' => 'Beautiful Downtown Apartment',
                'slug' => 'beautiful-downtown-apartment',
                'description' => 'A stunning 2-bedroom apartment in the heart of downtown with modern amenities and city views.',
                'type' => 'rent',
                'property_type' => 'apartment',
                'price' => 2500.00,
                'area' => 1200.00,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'address' => '123 Main Street',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'published_at' => now()
            ],
            [
                'title' => 'Luxury Family House',
                'slug' => 'luxury-family-house',
                'description' => 'Spacious 4-bedroom house perfect for families with a large backyard and modern kitchen.',
                'type' => 'sale',
                'property_type' => 'house',
                'price' => 750000.00,
                'area' => 2500.00,
                'bedrooms' => 4,
                'bathrooms' => 3,
                'address' => '456 Oak Avenue',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'postal_code' => '90210',
                'published_at' => now()
            ],
            [
                'title' => 'Modern Condo with Pool',
                'slug' => 'modern-condo-with-pool',
                'description' => 'Contemporary 1-bedroom condo with access to pool, gym, and rooftop terrace.',
                'type' => 'rent',
                'property_type' => 'condo',
                'price' => 1800.00,
                'area' => 800.00,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'address' => '789 Beach Boulevard',
                'city' => 'Miami',
                'state' => 'FL',
                'postal_code' => '33101',
                'published_at' => now()
            ]
        ];

        foreach ($properties as $propertyData) {
            $propertyData['user_id'] = $user->id;
            $propertyData['category_id'] = $category->id;
            $propertyData['location_id'] = $location->id;

            Property::create($propertyData);
        }

        $this->command->info('Created ' . count($properties) . ' sample properties');
    }
}