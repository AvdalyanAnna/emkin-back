<?php

namespace Database\Seeders;

use Doctrine\DBAL\Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * Class DatabaseSeeder
 * @package Database\Seeders
 */
class DatabaseSeeder extends Seeder
{

    /**
     * Seed the application's database.
     *
     * @throws Exception
     * @return void
     */
    public function run()
    {
        Model::unguard();

        \Schema::disableForeignKeyConstraints();

        /**
         * Удаляет все таблици
         */
        foreach($this->getTargetTableNames() as $table) \DB::table($table)->truncate();

        \Schema::enableForeignKeyConstraints();

        $this->call([
            LanguageSeeder::class,
            CountrySeeder::class,
            RoleSeeder::class,
            RolesAndPermissionsSeeder::class,
            AdminSeeder::class,
            CategorySeeder::class,
            UserSeeder::class,
            SupportThemeSeeder::class
        ]);

        Model::reguard();
    }

    /**
     * Возврошает Все имена Таблиц
     *
     * @throws Exception
     * @return mixed
     */
    private function getTargetTableNames(): array
    {
        return array_diff($this->getAllTableNames(), ['migrations']);
    }

    /**
     * @throws Exception
     * @return array
     */
    private function getAllTableNames(): array
    {
        return \DB::connection()->getDoctrineSchemaManager()->listTableNames();
    }
}
