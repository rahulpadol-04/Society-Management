<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_heads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->enum('type', ['fixed', 'per_sqft', 'per_unit', 'percentage'])->default('fixed');
            $table->decimal('amount', 12, 2)->default(0);
            $table->boolean('is_taxable')->default(false);
            $table->decimal('gst_percentage', 5, 2)->nullable();
            $table->enum('frequency', ['monthly', 'quarterly', 'half_yearly', 'yearly', 'one_time'])->default('monthly');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'is_active']);
        });

        Schema::create('maintenance_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('bill_number')->unique();
            $table->unsignedBigInteger('flat_id')->nullable()->index();          // soft link
            $table->unsignedBigInteger('user_id')->nullable()->index();          // soft link
            $table->string('period');                                             // e.g. '2026-06'
            $table->date('bill_date');
            $table->date('due_date');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('late_fee', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->enum('status', ['unpaid', 'partial', 'paid', 'overdue', 'cancelled'])->default('unpaid');
            $table->json('line_items')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'status']);
            $table->index(['society_id', 'period']);
        });

        Schema::create('maintenance_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('receipt_number')->unique();
            $table->foreignId('maintenance_bill_id')->constrained('maintenance_bills')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['cash', 'cheque', 'online', 'upi', 'card', 'bank_transfer'])->default('cash');
            $table->string('reference')->nullable();
            $table->datetime('paid_at');
            $table->unsignedBigInteger('recorded_by')->nullable()->index();      // soft link
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('society_id');
            $table->index('maintenance_bill_id');
        });

        Schema::create('late_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('maintenance_bill_id')->constrained('maintenance_bills')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->text('reason')->nullable();
            $table->date('applied_on');
            $table->timestamps();

            $table->index('maintenance_bill_id');
        });

        Schema::create('invoice_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('name');
            $table->longText('header_html')->nullable();
            $table->longText('footer_html')->nullable();
            $table->text('terms')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['society_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_templates');
        Schema::dropIfExists('late_fees');
        Schema::dropIfExists('maintenance_payments');
        Schema::dropIfExists('maintenance_bills');
        Schema::dropIfExists('maintenance_heads');
    }
};
