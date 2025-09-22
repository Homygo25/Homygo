<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Location;
use App\Models\Property;
use App\Models\User;

class CagayanDeOroSeeder extends Seeder
{
    /**
     * Run the database seeds for Cagayan de Oro City
     */
    public function run(): void
    {
        // Create property categories
        $categories = [
            [
                'name' => 'Residential',
                'slug' => 'residential',
                'children' => [
                    ['name' => 'Houses', 'slug' => 'houses'],
                    ['name' => 'Apartments', 'slug' => 'apartments'],
                    ['name' => 'Condominiums', 'slug' => 'condominiums'],
                    ['name' => 'Townhouses', 'slug' => 'townhouses'],
                    ['name' => 'Duplexes', 'slug' => 'duplexes'],
                ]
            ],
            [
                'name' => 'Commercial',
                'slug' => 'commercial',
                'children' => [
                    ['name' => 'Office Buildings', 'slug' => 'office-buildings'],
                    ['name' => 'Retail Spaces', 'slug' => 'retail-spaces'],
                    ['name' => 'Warehouses', 'slug' => 'warehouses'],
                    ['name' => 'Restaurants', 'slug' => 'restaurants'],
                ]
            ],
            [
                'name' => 'Land',
                'slug' => 'land',
                'children' => [
                    ['name' => 'Residential Lots', 'slug' => 'residential-lots'],
                    ['name' => 'Commercial Lots', 'slug' => 'commercial-lots'],
                    ['name' => 'Agricultural Land', 'slug' => 'agricultural-land'],
                ]
            ]
        ];

        foreach ($categories as $categoryData) {
            $category = Category::create([
                'name' => $categoryData['name'],
                'slug' => $categoryData['slug'],
            ]);

            foreach ($categoryData['children'] as $childData) {
                Category::create([
                    'name' => $childData['name'],
                    'slug' => $childData['slug'],
                    'parent_id' => $category->id,
                ]);
            }
        }

        // Create Cagayan de Oro City locations (barangays)
        $cdoLocation = Location::create([
            'name' => 'Cagayan de Oro City',
            'slug' => 'cagayan-de-oro-city',
            'type' => 'city',
        ]);

        $barangays = [
            // Popular residential areas
            'Cogon',
            'Carmen',
            'Divisoria',
            'Lapasan',
            'Nazareth',
            'Macasandig',
            'Kauswagan',
            'Gusa',
            'Pueblo de Oro',
            'Lumbia',
            'Tablon',
            'Bulua',
            'Iponan',
            'Camaman-an',
            'Balulang',
            'Patag',
            'Bugo',
            'Consolacion',
            'Indahag',
            'Macabalan',
            'Balubal',
            'Bayabas',
            'Bayonga',
            'Besang',
            'Bonbon',
            'Canitoan',
            'Capitan Angel',
            'F.S. Catanico',
            'Dansolihon',
            'Fatima',
            'Pagatpatan',
            'Puerto',
            'Puntod',
            'San Simon',
            'Tag-ulo',
            'Tignapoloan',
            'Tagpangi',
            'Tumpagon',
            'Upper Balulang',
            'Upper Carmen',
            'Villa Angela',
        ];

        foreach ($barangays as $barangay) {
            Location::create([
                'name' => $barangay,
                'slug' => strtolower(str_replace([' ', '.'], ['-', ''], $barangay)),
                'type' => 'barangay',
                'parent_id' => $cdoLocation->id,
            ]);
        }

        // Create sample users
        $users = [
            [
                'name' => 'Juan Dela Cruz',
                'email' => 'juan@example.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria@example.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ],
            [
                'name' => 'CDO Realty Admin',
                'email' => 'admin@cdorealty.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        // Get created data for property creation
        $houseCategory = Category::where('slug', 'houses')->first();
        $condoCategory = Category::where('slug', 'condominiums')->first();
        $commercialCategory = Category::where('slug', 'retail-spaces')->first();
        
        $cogonLocation = Location::where('slug', 'cogon')->first();
        $lapaLocation = Location::where('slug', 'lapasan')->first();
        $nazarethLocation = Location::where('slug', 'nazareth')->first();
        $gusaLocation = Location::where('slug', 'gusa')->first();
        $puebloLocation = Location::where('slug', 'pueblo-de-oro')->first();

        $sampleUser = User::first();

        // Create sample properties for Cagayan de Oro City
        $properties = [
            [
                'title' => 'Modern 3BR House in Cogon',
                'description' => 'Beautiful 3-bedroom house with modern amenities located in the heart of Cogon. Perfect for families looking for convenience and comfort. Near schools, malls, and business districts.',
                'price' => 2500000,
                'property_type' => 'house',
                'listing_type' => 'sale',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area' => 120,
                'address' => 'Cogon, Cagayan de Oro City',
                'latitude' => 8.4542,
                'longitude' => 124.6319,
                'is_featured' => true,
                'status' => 'active',
                'category_id' => $houseCategory->id,
                'location_id' => $cogonLocation->id,
                'user_id' => $sampleUser->id,
            ],
            [
                'title' => '2BR Condo Unit in Pueblo de Oro',
                'description' => 'Luxury 2-bedroom condominium unit in the prestigious Pueblo de Oro area. With pool, gym, and 24/7 security. Perfect for young professionals and small families.',
                'price' => 25000,
                'property_type' => 'condo',
                'listing_type' => 'rent',
                'bedrooms' => 2,
                'bathrooms' => 1,
                'area' => 65,
                'address' => 'Pueblo de Oro, Cagayan de Oro City',
                'latitude' => 8.4823,
                'longitude' => 124.6511,
                'is_featured' => true,
                'status' => 'active',
                'category_id' => $condoCategory->id,
                'location_id' => $puebloLocation->id,
                'user_id' => $sampleUser->id,
            ],
            [
                'title' => 'Commercial Space in Lapasan',
                'description' => 'Prime commercial space available for lease in busy Lapasan area. Ideal for retail business, restaurant, or office. High foot traffic and excellent visibility.',
                'price' => 45000,
                'property_type' => 'commercial',
                'listing_type' => 'rent',
                'bedrooms' => 0,
                'bathrooms' => 2,
                'area' => 85,
                'address' => 'Lapasan, Cagayan de Oro City',
                'latitude' => 8.4721,
                'longitude' => 124.6421,
                'is_featured' => false,
                'status' => 'active',
                'category_id' => $commercialCategory->id,
                'location_id' => $lapaLocation->id,
                'user_id' => $sampleUser->id,
            ],
            [
                'title' => 'Family Home in Nazareth',
                'description' => 'Spacious 4-bedroom family home in peaceful Nazareth neighborhood. Large lot with garden space, covered parking for 2 cars. Near churches and schools.',
                'price' => 3200000,
                'property_type' => 'house',
                'listing_type' => 'sale',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'area' => 150,
                'address' => 'Nazareth, Cagayan de Oro City',
                'latitude' => 8.4612,
                'longitude' => 124.6398,
                'is_featured' => true,
                'status' => 'active',
                'category_id' => $houseCategory->id,
                'location_id' => $nazarethLocation->id,
                'user_id' => $sampleUser->id,
            ],
            [
                'title' => 'Affordable Apartment in Gusa',
                'description' => 'Clean and affordable 1-bedroom apartment perfect for students or young professionals. Near Xavier University and jeepney routes. Fully furnished option available.',
                'price' => 8000,
                'property_type' => 'apartment',
                'listing_type' => 'rent',
                'bedrooms' => 1,
                'bathrooms' => 1,
                'area' => 30,
                'address' => 'Gusa, Cagayan de Oro City',
                'latitude' => 8.4756,
                'longitude' => 124.6573,
                'is_featured' => false,
                'status' => 'active',
                'category_id' => Category::where('slug', 'apartments')->first()->id,
                'location_id' => $gusaLocation->id,
                'user_id' => $sampleUser->id,
            ],
        ];

        foreach ($properties as $propertyData) {
            Property::create($propertyData);
        }

        $this->command->info('Cagayan de Oro City data seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . Category::count() . ' categories');
        $this->command->info('- ' . Location::count() . ' locations');
        $this->command->info('- ' . User::count() . ' users');
        $this->command->info('- ' . Property::count() . ' properties');
    }
}