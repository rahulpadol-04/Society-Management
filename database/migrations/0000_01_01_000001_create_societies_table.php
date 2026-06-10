<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('societies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();             // subdomain identifier
            $table->string('registration_number')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('logo')->nullable();

            // Address
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('India');
            $table->string('postal_code', 12)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('timezone')->default('Asia/Kolkata');
            $table->char('currency', 3)->default('INR');

            // Subscription snapshot (subscriptions table holds the full history)
            $table->foreignId('current_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->enum('subscription_status', ['trial', 'active', 'past_due', 'suspended', 'cancelled'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();

            $table->enum('status', ['active', 'suspended', 'pending'])->default('active');
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('subscription_status');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('societies');
    }
};
