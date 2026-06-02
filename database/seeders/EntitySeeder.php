<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Entity;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str; // تم إضافة حزمة الـ سبيس لتوليد السلغ هنا

class EntitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get fallback owner user from previous seeder sequence
        $owner = User::where('role', 'owner')->first();
        $ownerId = $owner ? $owner->id : null;

        // Fetch Subcategories safely mapping to real ecosystem domains
        $footballSub = Category::where('name', 'Football Fields')->first();
        $foodSub     = Category::where('name', 'Traditional Food')->first();
        $weddingSub  = Category::where('name', 'Wedding Halls')->first();

        if ($footballSub) {
            Entity::create([
                'category_id' => $footballSub->id,
                'user_id'     => $ownerId,
                'name'        => 'Al-Diyafa Green Stadium',
                'slug'        => Str::slug('Al-Diyafa Green Stadium'), // تم الإضافة هنا
                'description' => 'Premium natural grass football field with full lighting facilities.',
                'address'     => 'An-Nabk, Green District',
                'latitude'    => 34.0200,
                'longitude'   => 36.7500,
                'price_range' => '50k - 80k SYP/Hour',
                'is_verified' => true,
                'status'      => 'approved',
            ]);
        }

        if ($foodSub) {
            Entity::create([
                'category_id' => $foodSub->id,
                'user_id'     => $ownerId,
                'name'        => 'Our Village Heritage Restaurant',
                'slug'        => Str::slug('Our Village Heritage Restaurant'), // تم الإضافة هنا
                'description' => 'Serving authentic traditional countryside breakfast and clay-oven pastries.',
                'address'     => 'Yabroud, Al-Qaa Square',
                'latitude'    => 33.9600,
                'longitude'   => 36.6500,
                'price_range' => 'Moderate',
                'is_verified' => true,
                'status'      => 'approved',
            ]);
        }

        if ($weddingSub) {
            Entity::create([
                'category_id' => $weddingSub->id,
                'user_id'     => $ownerId,
                'name'        => 'Al-Yasmine Outdoor Estate',
                'slug'        => Str::slug('Al-Yasmine Outdoor Estate'), // تم الإضافة هنا
                'description' => 'Spacious countryside summer farm tailored for weddings and upscale social banquets.',
                'address'     => 'Deir Atiyah, Western Orchards',
                'latitude'    => 34.1000,
                'longitude'   => 36.8000,
                'price_range' => 'Premium',
                'is_verified' => false,
                'status'      => 'approved',
            ]);
        }
    }
}
