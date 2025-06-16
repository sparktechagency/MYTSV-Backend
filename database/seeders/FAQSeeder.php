<?php
namespace Database\Seeders;

use App\Models\FAQ;
use Illuminate\Database\Seeder;

class FAQSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            'How do I create an account?'  => 'To create an account, click on the "Sign Up" button on the homepage, fill in the required details, and follow the verification steps sent to your email.',
            'Is the app free to use?'      => 'Yes, the app is free to download and use.',
            'How can I reset my password?' => 'Go to the login screen and click on "Forgot Password." Enter your registered email address, and we\'ll send you a link to reset your password.',
            'How can I contact support?'   => 'You can contact our support team by navigating to the "Help" section in the app and submitting a support ticket or emailing us at support@example.com.',
            'Is my data secure?'           => 'Yes, we take data security seriously. All personal data is encrypted and stored securely following industry standards.',
        ];
        foreach ($faqs as $key => $value) {
            FAQ::create([
                'question' => $key,
                'answer'   => $value,
            ]);
        }
    }
}
