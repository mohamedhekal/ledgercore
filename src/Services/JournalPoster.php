<?php

declare(strict_types=1);

namespace Hekal\LedgerCore\Services;

use Hekal\LedgerCore\Events\JournalPosted;
use Hekal\LedgerCore\Exceptions\LedgerException;
use Hekal\LedgerCore\Models\Account;
use Hekal\LedgerCore\Models\Balance;
use Hekal\LedgerCore\Models\FiscalPeriod;
use Hekal\LedgerCore\Models\Journal;
use Hekal\LedgerCore\Models\JournalLine;
use Hekal\LedgerCore\Support\JournalLineDraft;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class JournalPoster
{
    /**
     * @param  list<JournalLineDraft>  $lines
     */
    public function post(
        string $postedOn,
        array $lines,
        ?string $memo = null,
        ?string $reference = null,
    ): Journal {
        if (count($lines) < 2) {
            throw new LedgerException('A journal requires at least two lines.');
        }

        $debit = 0;
        $credit = 0;
        foreach ($lines as $line) {
            $this->assertLine($line);
            $debit += $line->debit;
            $credit += $line->credit;
        }

        if ($debit !== $credit || $debit === 0) {
            throw LedgerException::unbalanced($debit, $credit);
        }

        return DB::transaction(function () use ($postedOn, $lines, $memo, $reference): Journal {
            $period = $this->requireOpenPeriod($postedOn);
            $accountIds = array_values(array_unique(array_map(
                static fn (JournalLineDraft $line): int => $line->accountId,
                $lines,
            )));
            sort($accountIds);

            /** @var list<Account> $accounts */
            $accounts = Account::query()
                ->whereIn('id', $accountIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->all();

            $byId = [];
            foreach ($accounts as $account) {
                if (! $account->is_active) {
                    throw LedgerException::accountInactive($account->code);
                }
                $byId[$account->id] = $account;
            }

            foreach ($accountIds as $accountId) {
                if (! isset($byId[$accountId])) {
                    throw new LedgerException("Account [{$accountId}] was not found.");
                }
            }

            $journal = Journal::query()->create([
                'uuid' => (string) Str::uuid(),
                'fiscal_period_id' => $period->id,
                'posted_on' => $postedOn,
                'memo' => $memo,
                'reference' => $reference,
                'posted_at' => now(),
            ]);

            foreach ($lines as $line) {
                JournalLine::query()->create([
                    'journal_id' => $journal->id,
                    'account_id' => $line->accountId,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                    'memo' => $line->memo,
                ]);

                $balance = Balance::query()
                    ->where('account_id', $line->accountId)
                    ->lockForUpdate()
                    ->first();

                if (! $balance instanceof Balance) {
                    Balance::query()->create([
                        'account_id' => $line->accountId,
                        'debit_total' => 0,
                        'credit_total' => 0,
                    ]);

                    $balance = Balance::query()
                        ->where('account_id', $line->accountId)
                        ->lockForUpdate()
                        ->first();
                }

                if (! $balance instanceof Balance) {
                    throw new LedgerException("Balance row missing for account [{$line->accountId}].");
                }

                $balance->debit_total += $line->debit;
                $balance->credit_total += $line->credit;
                $balance->save();
            }

            $journal->load('lines');
            event(new JournalPosted($journal));

            return $journal;
        });
    }

    public function reverse(Journal $journal, ?string $memo = null): Journal
    {
        $existing = Journal::query()->where('reverses_journal_id', $journal->id)->exists();
        if ($existing) {
            throw LedgerException::alreadyReversed($journal->uuid);
        }

        $journal->loadMissing('lines');

        $lines = [];
        foreach ($journal->lines as $line) {
            $lines[] = new JournalLineDraft(
                accountId: $line->account_id,
                debit: $line->credit,
                credit: $line->debit,
                memo: $line->memo,
            );
        }

        $reversal = $this->post(
            postedOn: $journal->posted_on->toDateString(),
            lines: $lines,
            memo: $memo ?? 'Reversal of '.$journal->uuid,
            reference: $journal->reference,
        );

        $reversal->forceFill(['reverses_journal_id' => $journal->id])->save();

        return $reversal->fresh(['lines']) ?? $reversal;
    }

    private function assertLine(JournalLineDraft $line): void
    {
        $hasDebit = $line->debit > 0;
        $hasCredit = $line->credit > 0;

        if ($hasDebit === $hasCredit || $line->debit < 0 || $line->credit < 0) {
            throw LedgerException::invalidLine();
        }
    }

    private function requireOpenPeriod(string $postedOn): FiscalPeriod
    {
        $period = FiscalPeriod::query()
            ->whereDate('starts_on', '<=', $postedOn)
            ->whereDate('ends_on', '>=', $postedOn)
            ->lockForUpdate()
            ->first();

        if (! $period instanceof FiscalPeriod) {
            throw LedgerException::periodNotFound($postedOn);
        }

        if ($period->is_closed) {
            throw LedgerException::periodClosed($period->name);
        }

        return $period;
    }
}
