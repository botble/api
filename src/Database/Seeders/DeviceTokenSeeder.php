<?php

namespace Botble\Api\Database\Seeders;

use Botble\Api\Models\DeviceToken;
use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DeviceTokenSeeder extends BaseSeeder
{
    public function run(): void
    {
        DeviceToken::truncate();

        $customers = Customer::query()->limit(10)->get();

        if ($customers->isEmpty()) {
            $this->command->warn('No customers found. Please run customer seeder first.');

            return;
        }

        $platforms = ['ios', 'android', 'web'];

        $data = [];

        foreach ($customers as $customer) {
            $numberOfDevices = rand(1, 3);

            for ($i = 0; $i < $numberOfDevices; $i++) {
                $platform = Arr::random($platforms);

                $data[] = [
                    'user_id' => $customer->id,
                    'user_type' => Customer::class,
                    'platform' => $platform,
                    'token' => $this->generateFakeDeviceToken($platform),
                    'app_version' => $this->fake()->randomElement(['1.0.0', '1.1.0', '1.2.0', '2.0.0']),
                    'device_id' => $this->generateDeviceId($platform),
                    'is_active' => $this->fake()->boolean(90),
                    'last_used_at' => $this->fake()->optional(0.7)->dateTimeBetween('-1 month', 'now'),
                    'created_at' => $this->fake()->dateTimeBetween('-3 months', 'now'),
                    'updated_at' => $this->fake()->dateTimeBetween('-1 month', 'now'),
                ];
            }
        }

        DB::table('device_tokens')->insert($data);

        $this->command->info('Device tokens seeded successfully: ' . count($data) . ' tokens created.');
    }

    private function generateFakeDeviceToken(string $platform): string
    {
        $faker = $this->fake();

        return match($platform) {
            'ios' => $faker->regexify('[a-f0-9]{64}'),
            'android' => 'f' . $faker->regexify('[a-zA-Z0-9_-]{50}') . ':APA91b' . $faker->regexify('[a-zA-Z0-9_-]{50}'),
            'web' => $faker->regexify('[a-zA-Z0-9_-]{100}'),
            default => $faker->sha256(),
        };
    }

    private function generateDeviceId(string $platform): string
    {
        $faker = $this->fake();

        return match($platform) {
            'ios' => $faker->uuid(),
            'android' => $faker->regexify('[a-f0-9]{16}'),
            'web' => $faker->uuid(),
            default => $faker->uuid(),
        };
    }
}
