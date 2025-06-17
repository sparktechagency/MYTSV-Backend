<?php
namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\AboutUs;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AboutUsSeeder::class,
            PageSeeder::class,
            FAQSeeder::class,
            ContactSeeder::class,
            CategorySeeder::class,
            BlogSeeder::class,
        ]);
    }
}
