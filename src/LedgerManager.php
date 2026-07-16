<?php

declare(strict_types=1);

namespace Hekal\LedgerCore;

use Hekal\LedgerCore\Models\Journal;
use Hekal\LedgerCore\Services\JournalPoster;
use Hekal\LedgerCore\Services\TrialBalance;
use Hekal\LedgerCore\Support\JournalLineDraft;

final class LedgerManager
{
    public function __construct(
        private readonly JournalPoster $poster,
        private readonly TrialBalance $trialBalance,
    ) {}

    /**
     * @param  list<JournalLineDraft>  $lines
     */
    public function post(
        string $postedOn,
        array $lines,
        ?string $memo = null,
        ?string $reference = null,
    ): Journal {
        return $this->poster->post($postedOn, $lines, $memo, $reference);
    }

    public function reverse(Journal $journal, ?string $memo = null): Journal
    {
        return $this->poster->reverse($journal, $memo);
    }

    /**
     * @return array{
     *     currency: string,
     *     lines: list<array{account_id: int, code: string, name: string, type: string, debit: int, credit: int}>,
     *     totals: array{debit: int, credit: int, balanced: bool}
     * }
     */
    public function trialBalance(): array
    {
        return $this->trialBalance->generate();
    }
}
