<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_passes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();

            $table->string('code')->unique();                          // QR token  VP-YYMM-XXXXX

            // Soft links — cross-module references (no FK constraint)
            $table->unsignedBigInteger('flat_id')->nullable()->index();
            $table->unsignedBigInteger('host_id')->nullable()->index(); // the resident

            $table->string('name');
            $table->string('phone')->nullable();
            $table->enum('type', ['guest', 'delivery', 'cab', 'service', 'vendor'])->default('guest');
            $table->string('purpose')->nullable();
            $table->string('vehicle_number')->nullable();

            $table->dateTime('expected_at')->nullable();
            $table->dateTime('valid_until')->nullable();
            $table->unsignedInteger('max_entries')->default(1);
            $table->unsignedInteger('entries_used')->default(0);

            $table->enum('status', ['pending', 'approved', 'rejected', 'expired', 'used'])->default('pending');

            // Soft link to the approving staff/admin
            $table->unsignedBigInteger('approved_by')->nullable()->index();
            $table->dateTime('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'status']);
        });

        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();

            // Within-module FK (nullable, set null on delete)
            $table->foreignId('visitor_pass_id')
                ->nullable()
                ->constrained('visitor_passes')
                ->nullOnDelete();

            // Soft links — cross-module / user references
            $table->unsignedBigInteger('flat_id')->nullable()->index();
            $table->unsignedBigInteger('guard_id')->nullable()->index(); // security guard user

            $table->string('name');
            $table->string('phone')->nullable();
            $table->enum('type', ['guest', 'delivery', 'cab', 'service', 'vendor'])->default('guest');
            $table->string('purpose')->nullable();
            $table->string('vehicle_number')->nullable();

            $table->string('photo')->nullable();
            $table->string('id_proof')->nullable();
            $table->string('gate')->nullable();

            $table->dateTime('checked_in_at');
            $table->dateTime('checked_out_at')->nullable();

            $table->enum('status', ['in', 'out'])->default('in');

            $table->timestamps();

            $table->index(['society_id', 'status']);
            $table->index(['society_id', 'checked_in_at']);
            $table->index('visitor_pass_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
        Schema::dropIfExists('visitor_passes');
    }
};
