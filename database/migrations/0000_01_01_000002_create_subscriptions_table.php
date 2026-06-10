<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans');

            $table->enum('status', ['trial', 'active', 'past_due', 'cancelled', 'expired'])->default('active');
            $table->decimal('amount', 12, 2)->default(0);
            $table->char('currency', 3)->default('INR');
            $table->enum('billing_cycle', ['trial', 'monthly', 'quarterly', 'annual'])->default('monthly');

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Payment gateway references
            $table->string('gateway')->nullable();
            $table->string('gateway_subscription_id')->nullable();
            $table->string('last_payment_id')->nullable();
            $table->timestamp('last_payment_at')->nullable();

            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['society_id', 'status']);
        });

        // Invoices the platform raises to societies for their SaaS subscription.
        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->char('currency', 3)->default('INR');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('gateway')->nullable();
            $table->string('gateway_payment_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
        Schema::dropIfExists('subscriptions');
    }
};
