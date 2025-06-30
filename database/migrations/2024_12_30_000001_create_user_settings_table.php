<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('user_settings')) {
            return;
        }

        Schema::create('user_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('user_type'); // customer, admin, etc.
            $table->unsignedBigInteger('user_id');
            $table->string('key'); // setting key like 'biometric_enabled', 'notifications', etc.
            $table->json('value'); // setting value stored as JSON
            $table->timestamps();

            $table->unique(['user_type', 'user_id', 'key']);
            $table->index(['user_type', 'user_id']);
            $table->index('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
