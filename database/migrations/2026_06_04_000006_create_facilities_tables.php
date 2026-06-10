<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['clubhouse', 'gym', 'pool', 'court', 'hall', 'other'])->default('other');
            $table->text('description')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->decimal('charge', 12, 2)->default(0);
            $table->boolean('requires_approval')->default(true);
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->unsignedInteger('slot_minutes')->default(60);
            $table->boolean('is_active')->default(true);
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'is_active']);
        });

        Schema::create('facility_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();        // soft link — booker
            $table->unsignedBigInteger('flat_id')->nullable();        // soft link — structure module
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('guests')->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'completed'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();    // soft link — users
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'status']);
            $table->index(['facility_id', 'booking_date']);
            $table->index('user_id');
            $table->index('flat_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_bookings');
        Schema::dropIfExists('facilities');
    }
};
