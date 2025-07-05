<?php

namespace Botble\Api\Database\Seeders;

use Botble\Api\Models\PersonalAccessToken;
use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PersonalAccessTokenSeeder extends BaseSeeder
{
    public function run(): void
    {
        PersonalAccessToken::truncate();

        $customers = Customer::query()->limit(15)->get();

        if ($customers->isEmpty()) {
            $this->command->warn('No customers found. Please run customer seeder first.');

            return;
        }

        $abilities = [
            ['*'],
            ['read', 'write'],
            ['read'],
            ['products:read', 'orders:read'],
            ['products:read', 'products:write', 'orders:read', 'orders:write'],
            ['profile:read', 'profile:write'],
            ['notifications:read', 'notifications:write'],
        ];

        $tokenNames = [
            'Mobile App',
            'Web App',
            'API Client',
            'Testing Token',
            'Development Token',
            'Third-party Integration',
            'Analytics Service',
            'Backup Service',
        ];

        $data = [];

        foreach ($customers as $customer) {
            $numberOfTokens = rand(1, 4);

            for ($i = 0; $i < $numberOfTokens; $i++) {
                $createdAt = $this->fake()->dateTimeBetween('-6 months', 'now');
                $lastUsedAt = $this->fake()->dateTimeBetween($createdAt, 'now');
                $expiresAt = $this->fake()->optional(0.3)->dateTimeBetween('now', '+1 year');

                $data[] = [
                    'tokenable_type' => Customer::class,
                    'tokenable_id' => $customer->id,
                    'name' => Arr::random($tokenNames) . ' #' . ($i + 1),
                    'token' => hash('sha256', Str::random(40)),
                    'abilities' => json_encode(Arr::random($abilities)),
                    'last_used_at' => $this->fake()->optional(0.7)->passthrough($lastUsedAt),
                    'expires_at' => $expiresAt,
                    'created_at' => $createdAt,
                    'updated_at' => $lastUsedAt,
                ];
            }
        }

        DB::table('personal_access_tokens')->insert($data);

        $this->command->info('Personal access tokens seeded successfully: ' . count($data) . ' tokens created.');
    }
}
