<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('name');
            $table->enum('channel', ['email', 'sms', 'whatsapp', 'push', 'in_app'])->default('email');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'channel']);
        });

        Schema::create('broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->json('channels');
            $table->enum('audience', ['all', 'owners', 'tenants', 'staff', 'residents'])->default('all');
            $table->enum('status', ['draft', 'queued', 'sending', 'sent', 'failed'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->unsignedBigInteger('created_by')->nullable()->index(); // SOFT LINK to users
            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'status']);
        });

        Schema::create('broadcast_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('broadcast_id')->constrained('broadcasts')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable()->index(); // SOFT LINK to users
            $table->string('channel');
            $table->enum('status', ['pending', 'sent', 'failed', 'read'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('broadcast_id');
        });

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('subject')->nullable();
            $table->enum('type', ['direct', 'group'])->default('direct');
            $table->unsignedBigInteger('created_by')->nullable()->index(); // SOFT LINK to users
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index('society_id');
        });

        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable()->index(); // SOFT LINK to users
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->unique(['conversation_id', 'user_id']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable()->index(); // SOFT LINK to users
            $table->text('body');
            $table->timestamps();

            $table->index('conversation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('broadcast_recipients');
        Schema::dropIfExists('broadcasts');
        Schema::dropIfExists('message_templates');
    }
};
