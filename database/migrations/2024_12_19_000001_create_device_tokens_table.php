<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('device_tokens')) {
            return;
        }

        Schema::create('device_tokens', function (Blueprint $table): void {
            $table->id();
            $table->string('token')->unique();
            $table->string('platform')->nullable(); // ios, android
            $table->string('app_version')->nullable();
            $table->string('device_id')->nullable();
            $table->string('user_type')->nullable(); // customer, admin, etc.
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['user_type', 'user_id']);
            $table->index(['platform', 'is_active']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
