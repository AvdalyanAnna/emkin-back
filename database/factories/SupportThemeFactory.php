<?php

namespace Database\Factories;

use App\Models\SupportTheme;
use Illuminate\Database\Eloquent\Factories\Factory;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupportTheme>
 */
class SupportThemeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SupportTheme::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    #[ArrayShape(['title' => "string"])] public function definition(): array
    {
        return [
            'title' => $this->faker->title()
        ];
    }
}
