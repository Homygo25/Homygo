<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            // Countries
            [
                'name' => 'United States',
                'slug' => 'united-states',
                'type' => 'country',
                'country_code' => 'US',
                'latitude' => 39.8283,
                'longitude' => -98.5795,
                'sort_order' => 1,
                'states' => [
                    [
                        'name' => 'California',
                        'slug' => 'california',
                        'type' => 'state',
                        'state_code' => 'CA',
                        'latitude' => 36.7783,
                        'longitude' => -119.4179,
                        'cities' => [
                            ['name' => 'Los Angeles', 'slug' => 'los-angeles', 'latitude' => 34.0522, 'longitude' => -118.2437],
                            ['name' => 'San Francisco', 'slug' => 'san-francisco', 'latitude' => 37.7749, 'longitude' => -122.4194],
                            ['name' => 'San Diego', 'slug' => 'san-diego', 'latitude' => 32.7157, 'longitude' => -117.1611],
                            ['name' => 'Sacramento', 'slug' => 'sacramento', 'latitude' => 38.5816, 'longitude' => -121.4944],
                        ]
                    ],
                    [
                        'name' => 'New York',
                        'slug' => 'new-york',
                        'type' => 'state',
                        'state_code' => 'NY',
                        'latitude' => 42.1657,
                        'longitude' => -74.9481,
                        'cities' => [
                            ['name' => 'New York City', 'slug' => 'new-york-city', 'latitude' => 40.7128, 'longitude' => -74.0060],
                            ['name' => 'Buffalo', 'slug' => 'buffalo', 'latitude' => 42.8864, 'longitude' => -78.8784],
                            ['name' => 'Rochester', 'slug' => 'rochester', 'latitude' => 43.1566, 'longitude' => -77.6088],
                            ['name' => 'Albany', 'slug' => 'albany', 'latitude' => 42.6526, 'longitude' => -73.7562],
                        ]
                    ],
                    [
                        'name' => 'Texas',
                        'slug' => 'texas',
                        'type' => 'state',
                        'state_code' => 'TX',
                        'latitude' => 31.9686,
                        'longitude' => -99.9018,
                        'cities' => [
                            ['name' => 'Houston', 'slug' => 'houston', 'latitude' => 29.7604, 'longitude' => -95.3698],
                            ['name' => 'Dallas', 'slug' => 'dallas', 'latitude' => 32.7767, 'longitude' => -96.7970],
                            ['name' => 'Austin', 'slug' => 'austin', 'latitude' => 30.2672, 'longitude' => -97.7431],
                            ['name' => 'San Antonio', 'slug' => 'san-antonio', 'latitude' => 29.4241, 'longitude' => -98.4936],
                        ]
                    ],
                    [
                        'name' => 'Florida',
                        'slug' => 'florida',
                        'type' => 'state',
                        'state_code' => 'FL',
                        'latitude' => 27.7663,
                        'longitude' => -81.6868,
                        'cities' => [
                            ['name' => 'Miami', 'slug' => 'miami', 'latitude' => 25.7617, 'longitude' => -80.1918],
                            ['name' => 'Orlando', 'slug' => 'orlando', 'latitude' => 28.5383, 'longitude' => -81.3792],
                            ['name' => 'Tampa', 'slug' => 'tampa', 'latitude' => 27.9506, 'longitude' => -82.4572],
                            ['name' => 'Jacksonville', 'slug' => 'jacksonville', 'latitude' => 30.3322, 'longitude' => -81.6557],
                        ]
                    ]
                ]
            ]
        ];

        foreach ($locations as $countryData) {
            $states = $countryData['states'] ?? [];
            unset($countryData['states']);
            
            $country = \App\Models\Location::create($countryData);
            
            foreach ($states as $stateData) {
                $cities = $stateData['cities'] ?? [];
                unset($stateData['cities']);
                
                $stateData['parent_id'] = $country->id;
                $stateData['country_code'] = $country->country_code;
                
                $state = \App\Models\Location::create($stateData);
                
                foreach ($cities as $cityData) {
                    $cityData['parent_id'] = $state->id;
                    $cityData['type'] = 'city';
                    $cityData['country_code'] = $country->country_code;
                    $cityData['state_code'] = $state->state_code;
                    $cityData['sort_order'] = 0;
                    
                    \App\Models\Location::create($cityData);
                }
            }
        }
    }
}
