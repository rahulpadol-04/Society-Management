<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('event');                 // created|updated|deleted|custom
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('url', 1000)->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
        });

        Schema::create('login_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email')->nullable();
            $table->enum('status', ['success', 'failed', 'logout', 'locked'])->default('success');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('device')->nullable();
            $table->string('platform')->nullable();
            $table->string('browser')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('logged_in_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        // Enforces "no reuse of last N passwords" policy.
        Schema::create('password_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('password');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_histories');
        Schema::dropIfExists('login_histories');
        Schema::dropIfExists('audit_logs');
    }
};
