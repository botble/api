<?php

namespace Botble\Api\Database\Seeders;

use Botble\Api\Models\PushNotification;
use Botble\Api\Models\PushNotificationRecipient;
use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PushNotificationSeeder extends BaseSeeder
{
    public function run(): void
    {
        PushNotification::truncate();
        PushNotificationRecipient::truncate();

        $customers = Customer::query()->limit(20)->get();

        if ($customers->isEmpty()) {
            $this->command->warn('No customers found. Please run customer seeder first.');

            return;
        }

        $notificationTypes = [
            'order' => [
                'titles' => [
                    'Order Confirmed',
                    'Order Shipped',
                    'Order Delivered',
                    'Order Processing',
                    'Payment Received',
                ],
                'messages' => [
                    'Your order #:order_id has been confirmed and is being processed.',
                    'Great news! Your order #:order_id has been shipped and is on its way.',
                    'Your order #:order_id has been delivered successfully.',
                    'We\'re currently processing your order #:order_id.',
                    'Payment for order #:order_id has been received successfully.',
                ],
            ],
            'promotion' => [
                'titles' => [
                    'Flash Sale Alert!',
                    'Exclusive Offer',
                    'Weekend Special',
                    'Limited Time Deal',
                    'New Arrivals',
                ],
                'messages' => [
                    'Save up to :discount% on selected items. Shop now!',
                    'You\'ve unlocked an exclusive :discount% discount. Use code: :code',
                    'Weekend special: Buy 2 get 1 free on all items!',
                    'Hurry! :product is now :discount% off for the next 24 hours.',
                    'Check out our latest collection of :category products!',
                ],
            ],
            'cart' => [
                'titles' => [
                    'Items in Your Cart',
                    'Don\'t Forget Your Cart',
                    'Cart Reminder',
                    'Complete Your Purchase',
                ],
                'messages' => [
                    'You have :count items waiting in your cart. Complete your purchase now!',
                    'Your cart items are selling fast. Secure them before they\'re gone!',
                    'Still thinking? The items in your cart are waiting for you.',
                    'Complete your purchase and enjoy free shipping on orders over $50!',
                ],
            ],
            'wishlist' => [
                'titles' => [
                    'Wishlist Alert',
                    'Price Drop Alert',
                    'Back in Stock',
                    'Wishlist Reminder',
                ],
                'messages' => [
                    ':product from your wishlist is now on sale!',
                    'Good news! :product is now :discount% off.',
                    ':product is back in stock. Get it before it\'s gone!',
                    'Items in your wishlist are waiting for you.',
                ],
            ],
            'account' => [
                'titles' => [
                    'Welcome!',
                    'Account Update',
                    'Security Alert',
                    'Profile Complete',
                ],
                'messages' => [
                    'Welcome to our store! Enjoy 10% off your first purchase.',
                    'Your account information has been updated successfully.',
                    'New login detected from :device. If this wasn\'t you, please secure your account.',
                    'Thank you for completing your profile! You\'ve earned 100 reward points.',
                ],
            ],
        ];

        $targetTypes = ['all', 'platform', 'user_type', 'user'];
        $platforms = ['ios', 'android', 'web'];
        $statuses = ['sent', 'failed', 'scheduled'];

        $notifications = [];
        $recipients = [];

        for ($i = 0; $i < 100; $i++) {
            $type = Arr::random(array_keys($notificationTypes));
            $typeData = $notificationTypes[$type];

            $title = Arr::random($typeData['titles']);
            $message = Arr::random($typeData['messages']);

            $message = str_replace(
                [':order_id', ':discount', ':code', ':product', ':count', ':category', ':device'],
                [
                    'ORD-' . rand(10000, 99999),
                    rand(10, 50),
                    strtoupper(Str::random(6)),
                    $this->fake()->word(),
                    rand(1, 5),
                    $this->fake()->word(),
                    $this->fake()->randomElement(['iPhone', 'Android Phone', 'Web Browser']),
                ],
                $message
            );

            $createdAt = $this->fake()->dateTimeBetween('-3 months', 'now');
            $scheduledAt = $this->fake()->optional(0.3)->dateTimeBetween($createdAt, '+1 week');
            $status = $scheduledAt && $scheduledAt > now() ? 'scheduled' : Arr::random(['sent', 'failed']);
            $sentAt = $status === 'sent' ? ($scheduledAt && $scheduledAt < now() ? $scheduledAt : $createdAt) : null;

            $data = $this->generateNotificationData($type);

            $targetType = Arr::random($targetTypes);
            $targetValue = match($targetType) {
                'all' => null,
                'platform' => Arr::random($platforms),
                'user_type' => Customer::class,
                'user' => null, // Will be handled with recipients
                default => null,
            };

            $sentCount = $status === 'sent' ? rand(50, 200) : 0;
            $deliveredCount = $status === 'sent' ? rand(40, min(190, $sentCount)) : 0;
            $readCount = $status === 'sent' ? rand(10, min(100, $deliveredCount)) : 0;
            $failedCount = $status === 'sent' ? rand(0, 10) : ($status === 'failed' ? rand(1, 50) : 0);

            $notification = [
                'id' => $i + 1,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'target_type' => $targetType,
                'target_value' => $targetValue,
                'data' => json_encode($data),
                'image_url' => $this->fake()->optional(0.3)->imageUrl(600, 400, 'products'),
                'action_url' => $this->fake()->optional(0.7)->url(),
                'status' => $status,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'delivered_count' => $deliveredCount,
                'read_count' => $readCount,
                'scheduled_at' => $scheduledAt,
                'sent_at' => $sentAt,
                'created_by' => $this->fake()->optional(0.8)->numberBetween(1, 5),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];

            $notifications[] = $notification;

            if ($targetType === 'user' || ($targetType !== 'all' && $this->fake()->boolean(70))) {
                $recipientCount = rand(1, 5);
                $selectedCustomers = $customers->random(min($recipientCount, $customers->count()));

                foreach ($selectedCustomers as $customer) {
                    $readAt = $sentAt && $this->fake()->boolean(70)
                        ? $this->fake()->dateTimeBetween($sentAt, 'now')
                        : null;

                    $recipients[] = [
                        'push_notification_id' => $i + 1,
                        'user_id' => $customer->id,
                        'user_type' => Customer::class,
                        'read_at' => $readAt,
                        'created_at' => $createdAt,
                        'updated_at' => $readAt ?: $createdAt,
                    ];
                }
            }
        }

        DB::table('push_notifications')->insert($notifications);
        DB::table('push_notification_recipients')->insert($recipients);

        $this->command->info('Push notifications seeded successfully: ' . count($notifications) . ' notifications and ' . count($recipients) . ' recipients created.');
    }

    private function generateNotificationData(string $type): array
    {
        $faker = $this->fake();

        return match($type) {
            'order' => [
                'order_id' => 'ORD-' . rand(10000, 99999),
                'order_status' => $faker->randomElement(['pending', 'processing', 'shipped', 'delivered']),
                'tracking_number' => $faker->optional()->regexify('[A-Z]{2}[0-9]{9}[A-Z]{2}'),
                'estimated_delivery' => $faker->boolean(70) ? $faker->dateTimeBetween('+1 day', '+1 week')->format('Y-m-d') : null,
            ],
            'promotion' => [
                'promotion_id' => 'PROMO-' . rand(1000, 9999),
                'discount_percentage' => rand(10, 50),
                'valid_until' => $faker->dateTimeBetween('+1 day', '+1 month')->format('Y-m-d H:i:s'),
                'minimum_purchase' => $faker->optional()->randomElement([50, 100, 150, 200]),
                'category' => $faker->optional()->word(),
            ],
            'cart' => [
                'cart_items_count' => rand(1, 10),
                'cart_total' => $faker->randomFloat(2, 10, 500),
                'abandoned_since' => $faker->dateTimeBetween('-7 days', '-1 day')->format('Y-m-d H:i:s'),
            ],
            'wishlist' => [
                'product_id' => rand(1, 100),
                'product_name' => $faker->words(3, true),
                'original_price' => $faker->randomFloat(2, 20, 200),
                'sale_price' => $faker->optional()->randomFloat(2, 10, 150),
                'stock_status' => $faker->randomElement(['in_stock', 'low_stock', 'out_of_stock']),
            ],
            'account' => [
                'action' => $faker->randomElement(['login', 'register', 'update_profile', 'change_password']),
                'ip_address' => $faker->ipv4(),
                'user_agent' => $faker->userAgent(),
                'location' => $faker->boolean(70) ? $faker->city() . ', ' . $faker->country() : null,
            ],
            default => [],
        };
    }
}
