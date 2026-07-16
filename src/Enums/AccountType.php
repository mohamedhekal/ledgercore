<?php

declare(strict_types=1);

namespace Hekal\LedgerCore\Enums;

enum AccountType: string
{
    case Asset = 'asset';
    case Liability = 'liability';
    case Equity = 'equity';
    case Revenue = 'revenue';
    case Expense = 'expense';

    public function isDebitNormal(): bool
    {
        return match ($this) {
            self::Asset, self::Expense => true,
            default => false,
        };
    }
}
