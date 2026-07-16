<?php

declare(strict_types=1);

namespace Hekal\LedgerCore\Services;

use Hekal\LedgerCore\Models\Account;
use Illuminate\Database\Eloquent\Collection;

final class TrialBalance
{
    /**
     * @return array{
     *     currency: string,
     *     lines: list<array{account_id: int, code: string, name: string, type: string, debit: int, credit: int}>,
     *     totals: array{debit: int, credit: int, balanced: bool}
     * }
     */
    public function generate(): array
    {
        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;

        /** @var Collection<int, Account> $accounts */
        $accounts = Account::with('balance')->orderBy('code')->get();

        foreach ($accounts as $account) {
            $balance = $account->balance;
            $debit = $balance === null ? 0 : $balance->debit_total;
            $credit = $balance === null ? 0 : $balance->credit_total;
            if ($debit === 0 && $credit === 0) {
                continue;
            }

            $rows[] = [
                'account_id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type->value,
                'debit' => $debit,
                'credit' => $credit,
            ];
            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        return [
            'currency' => (string) config('ledgercore.currency', 'USD'),
            'lines' => $rows,
            'totals' => [
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'balanced' => $totalDebit === $totalCredit,
            ],
        ];
    }
}
