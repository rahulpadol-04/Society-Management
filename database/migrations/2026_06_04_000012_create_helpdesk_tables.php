<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('ticket_number')->unique();
            $table->string('subject');
            $table->text('description')->nullable();
            $table->enum('category', ['general', 'technical', 'billing', 'facility', 'security', 'account', 'other'])->default('general');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'on_hold', 'resolved', 'closed'])->default('open');

            // Soft links to users (cross-module references — nullable, no FK constraint).
            $table->unsignedBigInteger('raised_by')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();

            $table->datetime('sla_due_at')->nullable();
            $table->boolean('sla_breached')->default(false);
            $table->unsignedTinyInteger('escalation_level')->default(0);
            $table->datetime('resolved_at')->nullable();
            $table->datetime('closed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'status']);
            $table->index(['society_id', 'priority']);
            $table->index('assigned_to');
            $table->index('raised_by');
        });

        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('support_ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();    // soft link
            $table->text('message');
            $table->boolean('is_internal')->default(false);
            $table->string('attachment')->nullable();
            $table->timestamps();

            $table->index('support_ticket_id');
            $table->index('user_id');
        });

        Schema::create('ticket_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('support_ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();    // soft link
            $table->string('action');
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('support_ticket_id');
            $table->index('user_id');
        });

        Schema::create('escalation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->unsignedTinyInteger('level');
            $table->string('name');
            $table->unsignedInteger('after_hours');
            $table->string('notify_role')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['society_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escalation_rules');
        Schema::dropIfExists('ticket_activities');
        Schema::dropIfExists('ticket_replies');
        Schema::dropIfExists('support_tickets');
    }
};
