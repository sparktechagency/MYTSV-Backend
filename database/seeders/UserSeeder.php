<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'              => 'System Admin',
            'email'             => 'admin@gmail.com',
            'role'              => 'ADMIN',
            'email_verified_at' => now(),
            'password'          => Hash::make('1234'),
        ]);
        User::create([
            'name'              => 'System User',
            'channel_name'      => 'Demo Channel',
            'email'             => 'user@gmail.com',
            'contact'           => '+9856425662',
            'bio'               => 'Lorem ipsum dolor sit amet consectetur. Pretium gravida risus enim suspendisse. Id id molestie dictum mauris tincidunt. Molestie posuere quam sapien luctus. Consectetur tincidunt tincidunt fermentum ut risus quam. Suspendisse vivamus laoreet ornare molestie iaculis vitae urna. Diam augue sed rhoncus nec egestas praesent sit orci. Dui ut morbi nulla ipsum eget semper quis non. Fames nullam aliquam pellentesque tortor nulla. Id eget dolor sagittis aenean proin.',
            'role'              => 'USER',
            'services'          => json_encode([
                'service1',
                'service2',
                'service3',
                'service4',
                'service5',
            ]),
            'email_verified_at' => now(),
            'password'          => Hash::make('1234'),
            'locations'         => json_encode([
                [
                    'type'     => 'head-office',
                    'location' => 'Dhaka, Bangladesh',
                    'lat'      => '23.8103',
                    'long'     => '90.4125',
                ],
                [
                    'type'     => 'branch',
                    'location' => 'Chittagong, Bangladesh',
                    'lat'      => '22.3569',
                    'long'     => '91.7832',
                ],
            ]),
        ]);
    }
}
