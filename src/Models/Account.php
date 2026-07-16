<?php

declare(strict_types=1);

namespace Hekal\LedgerCore\Models;

use Hekal\LedgerCore\Enums\AccountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property AccountType $type
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Balance|null $balance
 */
final class Account extends Model
{
    protected $table = 'lc_accounts';

    protected $fillable = [
        'code',
        'name',
        'type',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasOne<Balance, $this>
     */
    public function balance(): HasOne
    {
        return $this->hasOne(Balance::class);
    }

    public function signedBalance(): int
    {
        $balance = $this->balance;
        if ($balance === null) {
            return 0;
        }

        $net = $balance->debit_total - $balance->credit_total;

        return $this->type->isDebitNormal() ? $net : -$net;
    }
}
