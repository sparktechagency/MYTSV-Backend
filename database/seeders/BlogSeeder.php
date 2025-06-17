<?php
namespace Database\Seeders;

use App\Models\Blog;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $blogs = [
            [
                'image'       => 'blog1.jpg',
                'title'       => 'Everything I’ve Learned About Life, Creativity, and Staying Sane in the Digital Age',
                'description' => '<p>Stay ahead in the fast-paced world of technology. This blog offers in-depth tutorials, coding best practices, project walkthroughs, and tips on using tools li<u>ke Laravel, React Native, Node.js, and</u> Git. Ideal for developers of all levels, this space breaks down complex topics into real-world, applicable knowledge you can use.</p>',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'image'       => 'blog2.jpg',
                'title'       => 'Building Real-World Projects with Laravel and React Native: A Full Journey from Start to Ship',
                'description' => '<p>This blog is a personal journal of t<strong>houghts, les</strong>sons, and life experiences. From navigating everyday challenges to celebrating small wins, I share storie<strong>s that inspir</strong>e self-reflection, growth, and <strong>mindfulness. </strong>Whether you&apos;re here for inspiration, connection, or curiosity &mdash; welc<u>ome to a spac</u>e where every voice matters.</p>',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'image'       => 'blog3.jpg',
                'title'       => 'What Traveling to 10 Countries Taught Me About People, Culture, and Myself',
                'description' => '<p>Mobile development made simple and effective. Learn h<sup>ow to build, d</sup>eploy, and scale mobile applications using frameworks like React Native and Flutter. F<sub>rom UI/UX t</sub>ips to backend integration and performance o<span style="color: rgb(84, 172, 210);"><strong>ptimization, this blog&nbsp;</strong></span>is designed to help you ship faster and smarter.</p>',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'image'       => 'blog4.jpg',
                'title'       => 'My Honest Guide to Budget Traveling Without Losing Your Mind (or Wallet)',
                'description' => '<p>A collection of stories, travel guides, and destination tips from around the world. Whether you&apos;re a solo traveler, a weekend wanderer, or planning your first trip, this blog shares<strong>&nbsp;practical advice, cultural insights, </strong>and personal moments that make travel unforgettable.</p>',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'image'       => 'blog5.jpg',
                'title'       => 'How Living Abroad Changed My Perspective on Home, Happiness, and Purpose',
                'description' => '<ul>
    <li>Learn how to grow a successful online business. This blog covers everything from starting a Shopify or Laravel eCommerce store, to mastering product marketing, payment gateways, logistics, an<strong>d customer engagement</strong>. Perfect for entrepreneurs, dropshippers, and small business owners.</li>
</ul>',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'image'       => 'blog6.jpg',
                'title'       => 'The Honest Truth About Trying to Balance Passion, Work, and Inner Peace',
                'description' => '<p>Empower your mind and body with science-backed wellness advice, habit-building tips, and mental health insights. This blog focuses on practical ways to improve your daily routine &mdash; wh<strong>ether it&rsquo;s through mindfulness, clean eating, exercise, or personal development.</strong></p>
<p><br></p>',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'image'       => 'blog7.jpg',
                'title'       => 'Why Sharing Your Voice Online Still Matters — Even in a World Full of Noise',
                'description' => '<p>A space for artists, designers, and creative <strong>thinkers. Explore tutorials </strong>on UI/UX design, branding inspiration, color theory, and creative processes. Whether you&apos;re a professional designer or <strong>a creative hobby</strong>ist, you&rsquo;ll find valuable ideas to fuel your imagination.</p>
<p><br></p>',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ];
        Blog::insert($blogs);
    }
}
