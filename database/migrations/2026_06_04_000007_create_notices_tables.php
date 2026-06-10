<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->enum('category', ['notice', 'announcement', 'circular', 'event'])->default('notice');
            $table->unsignedBigInteger('author_id')->nullable()->index(); // soft link to users
            $table->enum('audience', ['all', 'owners', 'tenants', 'staff'])->default('all');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->boolean('pinned')->default(false);
            $table->timestamp('event_at')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'category']);
            $table->index(['society_id', 'is_published']);
        });

        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('notice_id')->nullable()->constrained('notices')->nullOnDelete();
            $table->string('question');
            $table->text('description')->nullable();
            $table->boolean('multiple_choice')->default(false);
            $table->timestamp('closes_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable()->index(); // soft link to users
            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'is_active']);
        });

        Schema::create('poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('poll_id')->constrained('polls')->cascadeOnDelete();
            $table->string('label');
            $table->unsignedInteger('votes_count')->default(0);
            $table->timestamps();

            $table->index('poll_id');
        });

        Schema::create('poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('poll_id')->constrained('polls')->cascadeOnDelete();
            $table->foreignId('poll_option_id')->constrained('poll_options')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable()->index(); // soft link to users
            $table->timestamps();

            $table->unique(['poll_option_id', 'user_id']);
            $table->index(['poll_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_votes');
        Schema::dropIfExists('poll_options');
        Schema::dropIfExists('polls');
        Schema::dropIfExists('notices');
    }
};
