<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('name');
            $table->string('company')->nullable();
            $table->enum('category', [
                'plumbing', 'electrical', 'housekeeping', 'security',
                'landscaping', 'elevator', 'pest_control', 'general', 'other',
            ])->default('general');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('gstin')->nullable();
            $table->text('address')->nullable();
            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('ratings_count')->default(0);
            $table->enum('status', ['active', 'inactive', 'blacklisted'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'category']);
            $table->index(['society_id', 'status']);
        });

        Schema::create('vendor_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('title');
            $table->string('contract_number')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('value', 14, 2)->default(0);
            $table->enum('status', ['active', 'expired', 'terminated', 'draft'])->default('active');
            $table->string('document')->nullable();
            $table->text('terms')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'vendor_id']);
        });

        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->unsignedBigInteger('vendor_id')->nullable();   // soft link
            $table->unsignedBigInteger('complaint_id')->nullable(); // soft link (optional)
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['open', 'assigned', 'in_progress', 'completed', 'cancelled'])->default('open');
            $table->decimal('amount', 14, 2)->default(0);
            $table->date('scheduled_for')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();  // soft link
            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'status']);
            $table->index('vendor_id');
        });

        Schema::create('vendor_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->unsignedBigInteger('work_order_id')->nullable(); // soft link
            $table->decimal('amount', 14, 2);
            $table->enum('method', ['cash', 'cheque', 'online', 'upi', 'bank_transfer'])->default('bank_transfer');
            $table->string('reference')->nullable();
            $table->dateTime('paid_at');
            $table->unsignedBigInteger('recorded_by')->nullable(); // soft link
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['society_id', 'vendor_id']);
        });

        Schema::create('vendor_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->unsignedBigInteger('work_order_id')->nullable(); // soft link
            $table->unsignedBigInteger('user_id')->nullable();       // soft link
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_ratings');
        Schema::dropIfExists('vendor_payments');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('vendor_contracts');
        Schema::dropIfExists('vendors');
    }
};
