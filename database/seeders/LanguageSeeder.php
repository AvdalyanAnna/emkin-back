<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @throws FileNotFoundException|\Throwable
     * @return void
     */
    public function run() {
        $languages = config('laravellocalization.supportedLocales');
        collect($languages)->map(function ($language, $code) {
            DB::table('languages')->insert([
                'name' => $language['name'],
                'code' => $code,
                'native' => $language['native'],
                'regional' => $language['regional'],
                'default' => $code === 'en',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        });
    }
}
