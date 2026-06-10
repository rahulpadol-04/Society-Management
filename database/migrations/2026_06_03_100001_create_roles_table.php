<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            // NULL = global/platform role (e.g. Super Admin). Otherwise the role
            // belongs to a specific society so each tenant can customise it.
            $table->foreignId('society_id')->nullable()->constrained('societies')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->enum('scope', ['global', 'society'])->default('society');
            $table->unsignedSmallInteger('level')->default(10);
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false); // system roles cannot be deleted
            $table->timestamps();

            $table->unique(['society_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
