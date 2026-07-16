<?php

declare(strict_types=1);

namespace Hekal\LedgerCore\Exceptions;

use RuntimeException;

final class LedgerException extends RuntimeException
{
    public static function unbalanced(int $debit, int $credit): self
    {
        return new self("Journal is unbalanced: debit={$debit} credit={$credit}.");
    }

    public static function invalidLine(): self
    {
        return new self('Each journal line must have either a debit or a credit (not both, not neither).');
    }

    public static function periodClosed(string $name): self
    {
        return new self("Fiscal period [{$name}] is closed.");
    }

    public static function periodNotFound(string $date): self
    {
        return new self("No open fiscal period covers [{$date}].");
    }

    public static function accountInactive(string $code): self
    {
        return new self("Account [{$code}] is inactive.");
    }

    public static function alreadyReversed(string $uuid): self
    {
        return new self("Journal [{$uuid}] has already been reversed.");
    }
}
