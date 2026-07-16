<?php

declare(strict_types=1);

use Hekal\LedgerCore\Enums\AccountType;
use Hekal\LedgerCore\Exceptions\LedgerException;
use Hekal\LedgerCore\Facades\Ledger;
use Hekal\LedgerCore\Models\Account;
use Hekal\LedgerCore\Models\Balance;
use Hekal\LedgerCore\Models\FiscalPeriod;
use Hekal\LedgerCore\Support\JournalLineDraft;

function seedChart(): array
{
    FiscalPeriod::query()->create([
        'name' => '2026-H1',
        'starts_on' => '2026-01-01',
        'ends_on' => '2026-06-30',
        'is_closed' => false,
    ]);

    $cash = Account::query()->create([
        'code' => '1000',
        'name' => 'Cash',
        'type' => AccountType::Asset,
    ]);
    $revenue = Account::query()->create([
        'code' => '4000',
        'name' => 'Sales',
        'type' => AccountType::Revenue,
    ]);
    $ar = Account::query()->create([
        'code' => '1100',
        'name' => 'Accounts Receivable',
        'type' => AccountType::Asset,
    ]);

    return compact('cash', 'revenue', 'ar');
}

it('posts a balanced journal and updates balances', function () {
    ['cash' => $cash, 'revenue' => $revenue] = seedChart();

    $journal = Ledger::post('2026-03-01', [
        new JournalLineDraft($cash->id, debit: 10_000),
        new JournalLineDraft($revenue->id, credit: 10_000),
    ], memo: 'Cash sale');

    expect($journal->lines)->toHaveCount(2)
        ->and(Balance::query()->where('account_id', $cash->id)->value('debit_total'))->toBe(10_000)
        ->and(Balance::query()->where('account_id', $revenue->id)->value('credit_total'))->toBe(10_000);

    $tb = Ledger::trialBalance();
    expect($tb['totals']['balanced'])->toBeTrue()
        ->and($tb['totals']['debit'])->toBe(10_000)
        ->and($tb['totals']['credit'])->toBe(10_000);
});

it('rejects unbalanced journals', function () {
    ['cash' => $cash, 'revenue' => $revenue] = seedChart();

    Ledger::post('2026-03-01', [
        new JournalLineDraft($cash->id, debit: 10_000),
        new JournalLineDraft($revenue->id, credit: 9_000),
    ]);
})->throws(LedgerException::class, 'unbalanced');

it('reverses a journal via compensating entry', function () {
    ['cash' => $cash, 'revenue' => $revenue] = seedChart();

    $original = Ledger::post('2026-03-01', [
        new JournalLineDraft($cash->id, debit: 5_000),
        new JournalLineDraft($revenue->id, credit: 5_000),
    ]);

    $reversal = Ledger::reverse($original);
    expect($reversal->reverses_journal_id)->toBe($original->id)
        ->and(Balance::query()->where('account_id', $cash->id)->value('debit_total'))->toBe(5_000)
        ->and(Balance::query()->where('account_id', $cash->id)->value('credit_total'))->toBe(5_000);

    Ledger::reverse($original);
})->throws(LedgerException::class, 'already been reversed');

it('blocks posting into a closed period', function () {
    ['cash' => $cash, 'revenue' => $revenue] = seedChart();
    FiscalPeriod::query()->update(['is_closed' => true]);

    Ledger::post('2026-03-01', [
        new JournalLineDraft($cash->id, debit: 1_000),
        new JournalLineDraft($revenue->id, credit: 1_000),
    ]);
})->throws(LedgerException::class, 'closed');
