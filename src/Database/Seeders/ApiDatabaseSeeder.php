<?php

namespace Botble\Api\Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Illuminate\Support\Facades\DB;

class ApiDatabaseSeeder extends BaseSeeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->call([
            DeviceTokenSeeder::class,
            PersonalAccessTokenSeeder::class,
            UserSettingSeeder::class,
            PushNotificationSeeder::class,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->finished();
    }
}
