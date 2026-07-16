<?php

declare(strict_types=1);

namespace Hekal\LedgerCore\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $fiscal_period_id
 * @property Carbon $posted_on
 * @property string|null $memo
 * @property string|null $reference
 * @property int|null $reverses_journal_id
 * @property Carbon $posted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read FiscalPeriod $fiscalPeriod
 * @property-read Collection<int, JournalLine> $lines
 */
final class Journal extends Model
{
    protected $table = 'lc_journals';

    protected $fillable = [
        'uuid',
        'fiscal_period_id',
        'posted_on',
        'memo',
        'reference',
        'reverses_journal_id',
        'posted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'posted_on' => 'date',
            'posted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<FiscalPeriod, $this>
     */
    public function fiscalPeriod(): BelongsTo
    {
        return $this->belongsTo(FiscalPeriod::class);
    }

    /**
     * @return HasMany<JournalLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    /**
     * @return BelongsTo<Journal, $this>
     */
    public function reverses(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reverses_journal_id');
    }
}
