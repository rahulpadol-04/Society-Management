<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();

            // Cross-module soft links (no constrained FK across modules).
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('flat_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable(); // self-ref for family members

            $table->enum('type', ['owner', 'tenant', 'family_member'])->default('owner');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('relation')->nullable();       // e.g. 'spouse', 'child'
            $table->boolean('is_primary')->default(false);
            $table->string('photo')->nullable();
            $table->date('move_in_date')->nullable();
            $table->date('move_out_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'moved_out'])->default('active');
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'type']);
            $table->index(['society_id', 'status']);
            $table->index('flat_id');
            $table->index('user_id');
            $table->index('parent_id');
        });

        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('resident_id')->constrained('residents')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->string('relation')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['society_id', 'resident_id']);
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();

            // Cross-module soft links.
            $table->unsignedBigInteger('flat_id')->nullable();
            $table->unsignedBigInteger('resident_id')->nullable();
            $table->unsignedBigInteger('parking_slot_id')->nullable();

            $table->enum('type', ['car', 'bike', 'other'])->default('car');
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('registration_number');
            $table->string('color')->nullable();
            $table->string('rfid_tag')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['society_id', 'registration_number']);
            $table->index(['society_id', 'type']);
            $table->index('flat_id');
            $table->index('resident_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('emergency_contacts');
        Schema::dropIfExists('residents');
    }
};
