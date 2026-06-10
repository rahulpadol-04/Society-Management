<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Society physical structure: towers/blocks/buildings -> floors -> flats/units,
 * plus parking slots and society documents. All tables are tenant-scoped by
 * society_id. Cross-module references (owner_id -> users, vehicle_id) are kept
 * as soft links (no FK) so modules stay independently migratable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('towers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->enum('type', ['tower', 'block', 'building', 'wing'])->default('tower');
            $table->unsignedSmallInteger('total_floors')->default(0);
            $table->unsignedSmallInteger('units_per_floor')->default(0);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['society_id', 'code']);
            $table->index(['society_id', 'status']);
        });

        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('tower_id')->constrained('towers')->cascadeOnDelete();
            $table->string('name');                          // "Ground", "1st Floor"
            $table->integer('number')->default(0);           // 0 = ground, negatives = basement
            $table->timestamps();

            $table->unique(['tower_id', 'number']);
            $table->index('society_id');
        });

        Schema::create('flats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('tower_id')->constrained('towers')->cascadeOnDelete();
            $table->foreignId('floor_id')->nullable()->constrained('floors')->nullOnDelete();
            $table->string('number');                         // "A-101"
            $table->string('type')->nullable();               // 1BHK / 2BHK / Shop / Office
            $table->decimal('carpet_area', 10, 2)->nullable();
            $table->decimal('built_up_area', 10, 2)->nullable();
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->enum('ownership', ['owner_occupied', 'rented', 'self', 'company'])->default('owner_occupied');
            $table->enum('status', ['occupied', 'vacant', 'under_construction', 'on_rent'])->default('vacant');
            $table->unsignedBigInteger('owner_id')->nullable();   // soft link -> users
            $table->decimal('maintenance_amount', 12, 2)->nullable(); // per-flat override
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['society_id', 'number']);
            $table->index(['society_id', 'tower_id']);
            $table->index(['society_id', 'status']);
            $table->index('owner_id');
        });

        Schema::create('parking_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('code');
            $table->enum('type', ['car', 'bike', 'visitor', 'ev', 'handicap'])->default('car');
            $table->string('location')->nullable();
            $table->foreignId('flat_id')->nullable()->constrained('flats')->nullOnDelete();
            $table->unsignedBigInteger('vehicle_id')->nullable();  // soft link -> vehicles
            $table->enum('status', ['available', 'assigned', 'reserved', 'blocked'])->default('available');
            $table->timestamps();

            $table->unique(['society_id', 'code']);
            $table->index(['society_id', 'status']);
        });

        Schema::create('society_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('title');
            $table->enum('category', ['legal', 'financial', 'noc', 'circular', 'agreement', 'other'])->default('other');
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();  // soft link -> users
            $table->boolean('is_public')->default(false);            // visible to residents
            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('society_documents');
        Schema::dropIfExists('parking_slots');
        Schema::dropIfExists('flats');
        Schema::dropIfExists('floors');
        Schema::dropIfExists('towers');
    }
};
