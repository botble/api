<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('push_notification_recipients')) {
            return;
        }

        Schema::create('push_notification_recipients', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('push_notification_id');
            $table->string('user_type', 50); // customer, admin, etc. - reduced length
            $table->unsignedBigInteger('user_id');
            $table->string('device_token')->nullable(); // The specific device token used
            $table->string('platform', 20)->nullable(); // android, ios - reduced length
            $table->string('status', 20)->default('sent'); // sent, delivered, failed, read - reduced length
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->json('fcm_response')->nullable(); // FCM response data
            $table->string('error_message')->nullable();
            $table->timestamps();

            $table->index(['push_notification_id', 'user_type', 'user_id'], 'pnr_notification_user_index');
            $table->index(['user_type', 'user_id', 'status'], 'pnr_user_status_index');
            $table->index(['user_type', 'user_id', 'read_at'], 'pnr_user_read_index');
            $table->index('status', 'pnr_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_notification_recipients');
    }
};
