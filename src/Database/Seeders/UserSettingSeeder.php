<?php

namespace Botble\Api\Database\Seeders;

use Botble\Api\Models\UserSetting;
use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UserSettingSeeder extends BaseSeeder
{
    public function run(): void
    {
        UserSetting::truncate();

        $customers = Customer::query()->limit(20)->get();

        if ($customers->isEmpty()) {
            $this->command->warn('No customers found. Please run customer seeder first.');

            return;
        }

        $settingKeys = [
            'notifications' => [
                'push_enabled' => ['true', 'false'],
                'email_enabled' => ['true', 'false'],
                'sms_enabled' => ['true', 'false'],
                'order_updates' => ['true', 'false'],
                'promotions' => ['true', 'false'],
                'newsletter' => ['true', 'false'],
                'quiet_hours_start' => ['22:00', '23:00', '21:00'],
                'quiet_hours_end' => ['07:00', '08:00', '06:00'],
            ],
            'preferences' => [
                'language' => ['en', 'es', 'fr', 'de', 'ja', 'zh'],
                'currency' => ['USD', 'EUR', 'GBP', 'JPY', 'CNY'],
                'theme' => ['light', 'dark', 'auto'],
                'font_size' => ['small', 'medium', 'large'],
                'compact_mode' => ['true', 'false'],
            ],
            'privacy' => [
                'show_profile' => ['public', 'friends', 'private'],
                'show_wishlist' => ['true', 'false'],
                'show_purchase_history' => ['true', 'false'],
                'analytics_enabled' => ['true', 'false'],
                'personalization_enabled' => ['true', 'false'],
            ],
            'app' => [
                'biometric_login' => ['true', 'false'],
                'remember_me' => ['true', 'false'],
                'auto_play_videos' => ['true', 'false'],
                'download_over_wifi_only' => ['true', 'false'],
                'cache_images' => ['true', 'false'],
                'sound_effects' => ['true', 'false'],
                'haptic_feedback' => ['true', 'false'],
            ],
            'shopping' => [
                'default_payment_method' => ['card', 'paypal', 'bank_transfer', 'cod'],
                'save_payment_info' => ['true', 'false'],
                'default_shipping_address' => ['home', 'work', 'other'],
                'express_checkout' => ['true', 'false'],
                'price_alerts' => ['true', 'false'],
                'wishlist_alerts' => ['true', 'false'],
            ],
        ];

        $data = [];

        foreach ($customers as $customer) {
            $numberOfSettings = rand(5, 15);
            $selectedCategories = Arr::random(array_keys($settingKeys), min(3, count($settingKeys)));

            $customerSettings = [];

            foreach ($selectedCategories as $category) {
                $categorySettings = $settingKeys[$category];
                $selectedKeys = Arr::random(array_keys($categorySettings), min($numberOfSettings, count($categorySettings)));

                foreach ($selectedKeys as $key) {
                    $fullKey = $category . '.' . $key;
                    if (! isset($customerSettings[$fullKey])) {
                        $customerSettings[$fullKey] = Arr::random($categorySettings[$key]);
                    }
                }
            }

            foreach ($customerSettings as $key => $value) {
                $data[] = [
                    'user_id' => $customer->id,
                    'user_type' => Customer::class,
                    'key' => $key,
                    'value' => json_encode($value),
                    'created_at' => $this->fake()->dateTimeBetween('-6 months', 'now'),
                    'updated_at' => $this->fake()->dateTimeBetween('-3 months', 'now'),
                ];
            }
        }

        DB::table('user_settings')->insert($data);

        $this->command->info('User settings seeded successfully: ' . count($data) . ' settings created.');
    }
}
