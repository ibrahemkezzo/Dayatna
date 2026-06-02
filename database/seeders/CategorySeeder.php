<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define Dayatna Core Ecosystem Categories
        $structure = [
            [
                'name' => 'Sports Stadiums',
                'icon' => 'fa-futbol',
                'subs' => ['Football Fields', 'Basketball Courts', 'Swimming Pools', 'Tennis Courts']
            ],
            [
                'name' => 'Restaurants & Cafes',
                'icon' => 'fa-utensils',
                'subs' => ['Traditional Food', 'Fast Food', 'Family Cafes', 'Rest Houses']
            ],
            [
                'name' => 'Event Halls',
                'icon' => 'fa-glass-cheers',
                'subs' => ['Wedding Halls', 'Conference Rooms', 'Outdoor Farms']
            ],
            [
                'name' => 'Local Shops',
                'icon' => 'fa-store',
                'subs' => ['Supermarkets', 'Pharmacies', 'Bakeries']
            ]
        ];

        foreach ($structure as $main) {
            // Create Main Category
            $parent = Category::create([
                'name'      => $main['name'],
                'slug'      => Str::slug($main['name']),
                'icon'      => $main['icon'],
                'parent_id' => null
            ]);

            // Create Subcategories tied to parent
            foreach ($main['subs'] as $subName) {
                Category::create([
                    'name'      => $subName,
                    'slug'      => Str::slug($subName),
                    'icon'      => null,
                    'parent_id' => $parent->id
                ]);
            }
        }

    }
}
