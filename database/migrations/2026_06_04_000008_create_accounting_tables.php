<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('code')->nullable();
            $table->string('name');
            $table->enum('type', ['asset', 'liability', 'equity', 'income', 'expense'])->default('asset');
            $table->string('subtype')->nullable();
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['society_id', 'code']);
            $table->index(['society_id', 'type']);
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->date('entry_date');
            $table->text('narration')->nullable();
            $table->enum('type', ['journal', 'income', 'expense', 'transfer', 'opening'])->default('journal');
            $table->enum('status', ['draft', 'posted'])->default('draft');
            $table->decimal('amount', 14, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable()->index();   // soft link -> users
            $table->unsignedBigInteger('posted_by')->nullable()->index();    // soft link -> users
            $table->dateTime('posted_at')->nullable();
            $table->string('source')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'status']);
            $table->index(['society_id', 'entry_date']);
        });

        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignId('ledger_account_id')->constrained('ledger_accounts');
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            $table->string('memo')->nullable();
            $table->timestamps();

            $table->index('journal_entry_id');
            $table->index('ledger_account_id');
        });

        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->unsignedBigInteger('ledger_account_id')->nullable()->index(); // soft link -> ledger_accounts
            $table->string('name');
            $table->enum('account_type', ['bank', 'cash'])->default('bank');
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc')->nullable();
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->decimal('current_balance', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'account_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('ledger_accounts');
    }
};
