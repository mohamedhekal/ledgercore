<?php

declare(strict_types=1);

namespace Hekal\LedgerCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $journal_id
 * @property int $account_id
 * @property int $debit
 * @property int $credit
 * @property string|null $memo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Journal $journal
 * @property-read Account $account
 */
final class JournalLine extends Model
{
    protected $table = 'lc_journal_lines';

    protected $fillable = [
        'journal_id',
        'account_id',
        'debit',
        'credit',
        'memo',
    ];

    /**
     * @return BelongsTo<Journal, $this>
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
