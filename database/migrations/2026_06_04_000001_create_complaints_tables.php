<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaint_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->unsignedInteger('sla_hours')->default(48);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['society_id', 'is_active']);
        });

        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->foreignId('complaint_category_id')->nullable()->constrained('complaint_categories')->nullOnDelete();
            $table->foreignId('raised_by')->constrained('users')->cascadeOnDelete();         // resident
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // staff/vendor
            $table->foreignId('flat_id')->nullable();                                         // soft link (structure module)

            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['open', 'assigned', 'in_progress', 'resolved', 'closed'])->default('open');

            $table->json('attachments')->nullable();
            $table->timestamp('sla_due_at')->nullable();
            $table->boolean('sla_breached')->default(false);
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('resolution_note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'status']);
            $table->index(['society_id', 'priority']);
            $table->index('assigned_to');
        });

        // Timeline of everything that happened to a complaint.
        Schema::create('complaint_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('complaint_id')->constrained('complaints')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');                 // created|assigned|status_changed|commented|resolved
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('complaint_id');
        });

        Schema::create('complaint_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('complaint_id')->constrained('complaints')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');    // 1-5
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique('complaint_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_feedback');
        Schema::dropIfExists('complaint_activities');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('complaint_categories');
    }
};
