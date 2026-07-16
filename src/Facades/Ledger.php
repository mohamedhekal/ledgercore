<?php

declare(strict_types=1);

namespace Hekal\LedgerCore\Facades;

use Hekal\LedgerCore\LedgerManager;
use Hekal\LedgerCore\Models\Journal;
use Hekal\LedgerCore\Support\JournalLineDraft;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Journal post(string $postedOn, array<int, JournalLineDraft> $lines, ?string $memo = null, ?string $reference = null)
 * @method static Journal reverse(Journal $journal, ?string $memo = null)
 * @method static array{currency: string, lines: list<array{account_id: int, code: string, name: string, type: string, debit: int, credit: int}>, totals: array{debit: int, credit: int, balanced: bool}} trialBalance()
 *
 * @see LedgerManager
 */
final class Ledger extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LedgerManager::class;
    }
}
