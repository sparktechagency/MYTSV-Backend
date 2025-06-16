<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Page::create([
            'type' => 'Terms & Conditions',
            'text' => '
                <h1>Terms and Conditions</h1>
                <p>By accessing or using our platform, you agree to be bound by the following terms and conditions. Please read them carefully.</p>

                <h2>1. Acceptance of Terms</h2>
                <p>By registering or using any part of our services, you accept these terms and agree to comply with them.</p>

                <h2>2. User Responsibilities</h2>
                <p>You agree to use the platform lawfully, not to violate any applicable laws, and to ensure that your use does not harm others or the platform.</p>

                <h2>3. Account Security</h2>
                <p>You are responsible for maintaining the confidentiality of your login credentials. Notify us immediately if you suspect unauthorized access.</p>

                <h2>4. Termination</h2>
                <p>We reserve the right to suspend or terminate your account if you violate these terms.</p>

                <h2>5. Modifications</h2>
                <p>We may update these terms at any time. Continued use of the platform after changes means you accept the updated terms.</p>

                <p>For questions or concerns, please <a href="contact-us">contact us</a>.</p>
            ',
        ]);
    }
}
