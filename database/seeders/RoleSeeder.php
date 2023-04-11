<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Class RoleSeeder
 * @package Database\Seeders
 */
class RoleSeeder extends Seeder
{
   /**
    * Run the database seeds.
    *
    * @return void
    */
   public function run()
   {
      collect(config('roles')['roles'])->each(function ($item, $key) {
         Role::query()->updateOrCreate([
            'name' => $key,
         ], [
            'display_name' => $item['display_name']['en'],
            'guard_name' => config('auth.defaults.guard'),
         ]);
      });
   }
}
