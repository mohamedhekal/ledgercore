<?php

declare(strict_types=1);

namespace Hekal\LedgerCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $account_id
 * @property int $debit_total
 * @property int $credit_total
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Account $account
 */
final class Balance extends Model
{
    protected $table = 'lc_balances';

    protected $fillable = [
        'account_id',
        'debit_total',
        'credit_total',
    ];

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
