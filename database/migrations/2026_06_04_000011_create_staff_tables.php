<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable()->index();      // soft link
            $table->string('name');
            $table->string('employee_code')->nullable();
            $table->string('designation')->nullable();
            $table->enum('department', ['security', 'housekeeping', 'maintenance', 'admin', 'gardening', 'plumbing', 'electrical', 'other'])->default('other');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->date('joining_date')->nullable();
            $table->decimal('salary', 12, 2)->default(0);
            $table->enum('shift', ['morning', 'evening', 'night', 'general'])->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave', 'terminated'])->default('active');
            $table->string('photo')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['society_id', 'employee_code']);
            $table->index(['society_id', 'department']);
            $table->index(['society_id', 'status']);
        });

        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('staff_member_id')->constrained('staff_members')->cascadeOnDelete();
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->enum('status', ['present', 'absent', 'half_day', 'leave', 'holiday'])->default('present');
            $table->decimal('hours', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('marked_by')->nullable()->index();    // soft link
            $table->timestamps();

            $table->unique(['staff_member_id', 'date']);
            $table->index(['society_id', 'date']);
        });

        Schema::create('staff_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('society_id');
        });

        Schema::create('staff_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('staff_member_id')->constrained('staff_members')->cascadeOnDelete();
            $table->enum('type', ['casual', 'sick', 'paid', 'unpaid'])->default('casual');
            $table->date('from_date');
            $table->date('to_date');
            $table->unsignedInteger('days')->default(1);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable()->index();  // soft link
            $table->timestamps();

            $table->index(['society_id', 'status']);
        });

        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('staff_member_id')->constrained('staff_members')->cascadeOnDelete();
            $table->string('period');                                         // YYYY-MM
            $table->decimal('basic', 12, 2)->default(0);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('net', 12, 2)->default(0);
            $table->unsignedInteger('days_present')->default(0);
            $table->unsignedInteger('days_absent')->default(0);
            $table->enum('status', ['draft', 'processed', 'paid'])->default('draft');
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['staff_member_id', 'period']);
            $table->index(['society_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('staff_leaves');
        Schema::dropIfExists('staff_shifts');
        Schema::dropIfExists('staff_attendances');
        Schema::dropIfExists('staff_members');
    }
};
