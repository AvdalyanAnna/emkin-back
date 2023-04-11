<?php

namespace Database\Factories;

use App\Enums\Advertise\AdvertiseStatistic;
use App\Enums\MediaCollections;
use App\Models\Advertise;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $cityCount = City::count();

        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'verified_at' => now(),
            'password' => 'password',
            'latitude' => fake()->latitude,
            'longitude' => fake()->longitude,
            'remember_token' => Str::random(10),
            'city_id' => rand(1, $cityCount)
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified(): static {
        return $this->state(function (array $attributes) {
            return [
                'verified_at' => null,
            ];
        });
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure(): static {

        $files = \Storage::disk('local')->files('img/avatar');

        $picture = public_path($files[rand(0, count($files)-1)]);

        return $this->afterCreating(function (User $user) use ($picture) {
            $disk = 'public';
            $user
                ->addMedia($picture)
                ->preservingOriginal()
                ->storingConversionsOnDisk($disk)
                ->toMediaCollection(MediaCollections::USER_AVATAR_COLLECTION, $disk);

            $user->assignRole('user');

            $advertises = Advertise::inRandomOrder()->limit(rand(0, 15))->pluck('id');

            $advertiseIds = [];
            foreach ($advertises as $id) {
                $advertiseIds[$id] = [
                    'type' => AdvertiseStatistic::Favorite,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            $user->favorites()->attach($advertiseIds);
        });
    }
}
