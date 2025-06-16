<?php
namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Contact::create([
            'email'   => 'example@gmail.com',
            'phone'   => '+98562354785',
            'address' => 'Dhaka, Bangladesh',
        ]);
    }
}
