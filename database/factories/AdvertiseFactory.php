<?php

namespace Database\Factories;

use App\Enums\MediaCollections;
use App\Models\Advertise;
use App\Models\FilterAnswer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class AdvertiseFactory
 * @package Database\Factories
 */
class AdvertiseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        $answersCount = FilterAnswer::count();
        $random_number_array = range(1, $answersCount);
        shuffle($random_number_array );
        $random_number_array = array_slice($random_number_array ,1, rand(0, 10));

        return [
            'name' => fake()->name(),
            'description' => fake()->realText(1000),
            'price' => fake()->randomFloat(2,100,100000),
            'link' => fake()->url(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'contacts' => rand(0, 2),
            'address' => fake()->address(),
            'filters' => $random_number_array,
            'type' => rand(0, 1),
            'inactively_date' => \Illuminate\Support\Carbon::now()->addDays(30),
            'status' => rand(0, 4),
            'show_phone' => rand(0, 10000),
            'show_details' => rand(0, 10000),
            'added_favorites' => rand(0, 10000),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure(): static {

        $answersCount = FilterAnswer::count();
        $random_number_array = range(1, $answersCount);
        shuffle($random_number_array );
        $random_number_array = array_slice($random_number_array ,1, rand(0, 10));

        $storage = \Storage::disk('local');
        $images = [
            $storage->files('img/preview'),
            $storage->files('img/product')
        ];

        return $this->afterCreating(function (Advertise $advertise) use ($random_number_array, $images) {
            $disk = 'public';
            $advertise->answers()->sync($random_number_array);
            $imageMultiple = $images[rand(0, 1)];

            foreach ($imageMultiple as $item) {
                $advertise
                    ->addMedia(public_path($item))
                    ->preservingOriginal()
                    ->storingConversionsOnDisk($disk)
                    ->toMediaCollection(MediaCollections::ADVERTISE_COLLECTION, $disk);
            }
        });
    }
}
