<?php
namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Database\Seeders\Deployment\CategorySeeder;
use Database\Seeders\Deployment\VideoSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Local Seeder
            UserSeeder::class,
            AboutUsSeeder::class,
            PageSeeder::class,
            FAQSeeder::class,
            ContactSeeder::class,
            CategorySeeder::class,
            BlogSeeder::class,
            PricingSeeder::class,
            BannerSeeder::class,
            SeoSeeder::class,
            StateSeeder::class,
            CitySeeder::class,
            SystemSettingSeeder::class,

            // Production Seeder
            // SystemSettingSeeder::class,
            // PageSeeder::class,
            // AboutUsSeeder::class,
            // FAQSeeder::class,
            // StateSeeder::class,
            // CitySeeder::class,
            // BannerSeeder::class,
            // ContactSeeder::class,
            // PricingSeeder::class,
            // SeoSeeder::class,
            // CategorySeeder::class,
            // BlogSeeder::class,
            // UserSeeder::class,

            // VideoSeeder::class,
            // CommentSeeder::class,
            // LikedVideoSeeder::class,
        ]);
    }
}
