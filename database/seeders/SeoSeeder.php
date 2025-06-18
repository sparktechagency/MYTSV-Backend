<?php
namespace Database\Seeders;

use App\Models\Seo;
use Illuminate\Database\Seeder;

class SeoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Seo::create([
            'title'       => 'Welcome to MyTSV',
            'description' => 'Discover a world of tutorials, personal stories, tech deep-dives, and creative inspiration. Learn and grow with every post.',
            'tags'        => json_encode([
                'blog', 'personal', 'tech', 'lifestyle', 'productivity',
            ]),
            'links'       => json_encode([
                [
                    'key'   => 'facebook',
                    'value' => [
                        'https://facebook.com/page1',
                        'https://facebook.com/page2',
                    ],
                ],
                [
                    'key'   => 'twitter',
                    'value' => ['https://twitter.com/myhandle'],
                ],
                [
                    'key'   => 'linkedin',
                    'value' => ['https://linkedin.com/company/page'],
                ],
            ]),

        ]);
    }
}
