<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lc_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lc_fiscal_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
            $table->unique(['starts_on', 'ends_on']);
        });

        Schema::create('lc_journals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('fiscal_period_id')->constrained('lc_fiscal_periods');
            $table->date('posted_on');
            $table->string('memo')->nullable();
            $table->string('reference')->nullable();
            $table->foreignId('reverses_journal_id')->nullable()->constrained('lc_journals')->nullOnDelete();
            $table->timestamp('posted_at');
            $table->timestamps();
        });

        Schema::create('lc_journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('lc_journals')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('lc_accounts');
            $table->unsignedBigInteger('debit')->default(0);
            $table->unsignedBigInteger('credit')->default(0);
            $table->string('memo')->nullable();
            $table->timestamps();
        });

        Schema::create('lc_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->unique()->constrained('lc_accounts')->cascadeOnDelete();
            $table->unsignedBigInteger('debit_total')->default(0);
            $table->unsignedBigInteger('credit_total')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lc_balances');
        Schema::dropIfExists('lc_journal_lines');
        Schema::dropIfExists('lc_journals');
        Schema::dropIfExists('lc_fiscal_periods');
        Schema::dropIfExists('lc_accounts');
    }
};
