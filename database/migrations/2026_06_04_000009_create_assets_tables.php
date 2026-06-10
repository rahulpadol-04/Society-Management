<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('depreciation_rate', 5, 2)->default(0);
            $table->unsignedInteger('useful_life_years')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['society_id', 'is_active']);
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->unsignedBigInteger('asset_category_id')->nullable()->index();
            $table->string('code')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->unsignedBigInteger('tower_id')->nullable()->index();
            $table->unsignedBigInteger('vendor_id')->nullable()->index();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 14, 2)->default(0);
            $table->decimal('salvage_value', 14, 2)->default(0);
            $table->enum('depreciation_method', ['straight_line', 'declining_balance', 'none'])->default('straight_line');
            $table->decimal('depreciation_rate', 5, 2)->nullable();
            $table->unsignedInteger('useful_life_years')->nullable();
            $table->decimal('current_value', 14, 2)->nullable();
            $table->enum('status', ['active', 'under_maintenance', 'retired', 'disposed'])->default('active');
            $table->date('warranty_until')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'status']);
        });

        Schema::create('asset_maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('title');
            $table->enum('frequency', ['weekly', 'monthly', 'quarterly', 'half_yearly', 'yearly', 'one_time'])->default('monthly');
            $table->date('next_due_date')->nullable();
            $table->date('last_done_date')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->unsignedBigInteger('vendor_id')->nullable()->index();
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->enum('status', ['scheduled', 'due', 'completed', 'overdue'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'asset_id']);
            $table->index(['society_id', 'status']);
        });

        Schema::create('asset_maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->unsignedBigInteger('asset_maintenance_schedule_id')->nullable()->index();
            $table->date('performed_on');
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('performed_by')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('asset_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance_logs');
        Schema::dropIfExists('asset_maintenance_schedules');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_categories');
    }
};
