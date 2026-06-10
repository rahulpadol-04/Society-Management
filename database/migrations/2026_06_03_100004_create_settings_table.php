<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Key/value settings. society_id NULL = global platform setting
        // (e.g. payment gateway, SMTP); otherwise a per-society override.
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->nullable()->constrained('societies')->cascadeOnDelete();
            $table->string('group')->default('general')->index();
            $table->string('key');
            $table->longText('value')->nullable();
            $table->string('type')->default('string'); // string|int|bool|json|encrypted
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->unique(['society_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
