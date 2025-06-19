<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('push_notifications')) {
            return;
        }

        Schema::create('push_notifications', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('general'); // general, order, promotion, system, etc.
            $table->string('target_type')->nullable(); // all, platform, user_type, user
            $table->string('target_value')->nullable(); // android/ios, customer/admin, user_id
            $table->string('action_url')->nullable();
            $table->string('image_url')->nullable();
            $table->json('data')->nullable(); // Additional custom data
            $table->string('status')->default('sent'); // sent, failed, scheduled
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('read_count')->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // Admin who sent the notification
            $table->timestamps();

            $table->index(['type', 'created_at']);
            $table->index(['status', 'scheduled_at']);
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_notifications');
    }
};
