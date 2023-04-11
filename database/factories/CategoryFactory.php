<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class CategoryFactory
 * @package Database\Factories
 */
class CategoryFactory extends Factory {

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    #[ArrayShape(['name' => "string"])] public function definition(): array {

        \DB::table('languages')
            ->get()
            ->collect()
            ->map(function ($item) use (&$name) {
                $name[$item->code] = fake($item->regional)->name();
            });

        return ['name' => $name];
    }
}
