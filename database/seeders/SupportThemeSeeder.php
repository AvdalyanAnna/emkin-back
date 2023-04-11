<?php

namespace Database\Seeders;

use App\Models\SupportTheme;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupportThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SupportTheme::factory()->count(rand(0, 10))->create();
    }
}
