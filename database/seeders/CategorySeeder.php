<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Property Categories
            [
                'name' => 'Real Estate',
                'slug' => 'real-estate',
                'description' => 'Properties for sale and rent',
                'icon' => 'fi-home',
                'color' => '#28a745',
                'type' => 'property',
                'sort_order' => 1,
                'children' => [
                    ['name' => 'Houses', 'slug' => 'houses', 'description' => 'Single family homes'],
                    ['name' => 'Apartments', 'slug' => 'apartments', 'description' => 'Apartment units'],
                    ['name' => 'Condos', 'slug' => 'condos', 'description' => 'Condominium units'],
                    ['name' => 'Townhouses', 'slug' => 'townhouses', 'description' => 'Townhouse properties'],
                    ['name' => 'Land', 'slug' => 'land', 'description' => 'Land for sale'],
                    ['name' => 'Commercial', 'slug' => 'commercial', 'description' => 'Commercial properties'],
                ]
            ],
            
            // Car Categories
            [
                'name' => 'Vehicles',
                'slug' => 'vehicles',
                'description' => 'Cars, trucks, and other vehicles',
                'icon' => 'fi-car',
                'color' => '#007bff',
                'type' => 'car',
                'sort_order' => 2,
                'children' => [
                    ['name' => 'Sedans', 'slug' => 'sedans', 'description' => 'Sedan vehicles'],
                    ['name' => 'SUVs', 'slug' => 'suvs', 'description' => 'Sport utility vehicles'],
                    ['name' => 'Trucks', 'slug' => 'trucks', 'description' => 'Pickup trucks'],
                    ['name' => 'Coupes', 'slug' => 'coupes', 'description' => 'Coupe vehicles'],
                    ['name' => 'Convertibles', 'slug' => 'convertibles', 'description' => 'Convertible cars'],
                    ['name' => 'Motorcycles', 'slug' => 'motorcycles', 'description' => 'Motorcycles and bikes'],
                ]
            ],
            
            // Contractor Categories
            [
                'name' => 'Home Services',
                'slug' => 'home-services',
                'description' => 'Professional home service providers',
                'icon' => 'fi-tool',
                'color' => '#ffc107',
                'type' => 'contractor',
                'sort_order' => 3,
                'children' => [
                    ['name' => 'Plumbing', 'slug' => 'plumbing', 'description' => 'Plumbing services'],
                    ['name' => 'Electrical', 'slug' => 'electrical', 'description' => 'Electrical work'],
                    ['name' => 'HVAC', 'slug' => 'hvac', 'description' => 'Heating, ventilation, and air conditioning'],
                    ['name' => 'Roofing', 'slug' => 'roofing', 'description' => 'Roofing services'],
                    ['name' => 'Landscaping', 'slug' => 'landscaping', 'description' => 'Landscaping and gardening'],
                    ['name' => 'Cleaning', 'slug' => 'cleaning', 'description' => 'Cleaning services'],
                    ['name' => 'Painting', 'slug' => 'painting', 'description' => 'Interior and exterior painting'],
                    ['name' => 'Carpentry', 'slug' => 'carpentry', 'description' => 'Carpentry and woodwork'],
                ]
            ]
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);
            
            $category = \App\Models\Category::create($categoryData);
            
            foreach ($children as $childData) {
                $childData['parent_id'] = $category->id;
                $childData['type'] = $category->type;
                $childData['icon'] = $category->icon;
                $childData['color'] = $category->color;
                $childData['sort_order'] = 0;
                
                \App\Models\Category::create($childData);
            }
        }
    }
}
