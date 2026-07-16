<?php

declare(strict_types=1);

namespace Hekal\LedgerCore\Support;

final readonly class JournalLineDraft
{
    public function __construct(
        public int $accountId,
        public int $debit = 0,
        public int $credit = 0,
        public ?string $memo = null,
    ) {}
}
