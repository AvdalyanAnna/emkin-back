<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Class AdminSeeder
 * @package Database\Seeders
 */
class AdminSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $faker = \Faker\Factory::create();

        User::create([
            'first_name' => $faker->firstName(),
            'last_name' => $faker->lastName(),
            'phone' => $faker->phoneNumber(),
            'email' => 'admin@admin.com',
            'verified_at' => now(),
            'password' => 'password',
            'latitude' => $faker->latitude,
            'longitude' => $faker->longitude,
        ])->assignRole('admin');

        User::create([
            'first_name' => $faker->firstName(),
            'last_name' => $faker->lastName(),
            'phone' => $faker->phoneNumber(),
            'email' => 'moderator@moderator.com',
            'verified_at' => now(),
            'password' => 'password',
            'latitude' => $faker->latitude,
            'longitude' => $faker->longitude,
        ])->assignRole('moderator');

        User::create([
            'first_name' => $faker->firstName(),
            'last_name' => $faker->lastName(),
            'phone' => $faker->phoneNumber(),
            'email' => 'user@user.com',
            'verified_at' => now(),
            'password' => 'password',
            'latitude' => $faker->latitude,
            'longitude' => $faker->longitude,
        ])->assignRole('user');
    }
}
