<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('billing_cycle', ['trial', 'monthly', 'quarterly', 'annual'])->default('monthly');
            $table->decimal('price', 12, 2)->default(0);
            $table->char('currency', 3)->default('INR');
            $table->unsignedInteger('trial_days')->default(0);

            // Plan limits (null = unlimited)
            $table->unsignedInteger('max_units')->nullable();
            $table->unsignedInteger('max_users')->nullable();
            $table->unsignedInteger('max_storage_mb')->nullable();

            // Feature flags toggled per plan (keys from config('communityos.features'))
            $table->json('features')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
