# LedgerCore


[![CI](https://github.com/mohamedhekal/ledgercore/actions/workflows/tests.yml/badge.svg)](https://github.com/mohamedhekal/ledgercore/actions)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-11%2F12-FF2D20.svg)](https://laravel.com/)

**Search terms:** laravel, accounting, ledger, double-entry, journal, erp, fintech, php, laravel-package, bookkeeping, general-ledger, accounting-engine.


Double-entry ledger engine for Laravel ERP/fintech apps: balanced journals, immutable postings, account balances, and fiscal period locks.

Amounts are **integer minor units** (cents). Single currency in v0.1.

## Installation

```bash
composer require hekal/ledgercore
php artisan vendor:publish --tag=ledgercore-config
php artisan migrate
```

## Chart + period

```php
use Hekal\LedgerCore\Models\Account;
use Hekal\LedgerCore\Models\FiscalPeriod;
use Hekal\LedgerCore\Enums\AccountType;

FiscalPeriod::create([
    'name' => '2026',
    'starts_on' => '2026-01-01',
    'ends_on' => '2026-12-31',
]);

$cash = Account::create(['code' => '1000', 'name' => 'Cash', 'type' => AccountType::Asset]);
$sales = Account::create(['code' => '4000', 'name' => 'Sales', 'type' => AccountType::Revenue]);
```

## Post a journal

```php
use Hekal\LedgerCore\Facades\Ledger;
use Hekal\LedgerCore\Support\JournalLineDraft;

$journal = Ledger::post('2026-03-15', [
    new JournalLineDraft($cash->id, debit: 10_000),
    new JournalLineDraft($sales->id, credit: 10_000),
], memo: 'Cash sale');

Ledger::reverse($journal);
$tb = Ledger::trialBalance(); // totals.balanced === true
```

## Guarantees

- Unbalanced journals throw
- Lines must be debit XOR credit
- Closed periods reject posts
- Postings are append-only; corrections use reversing journals
- Account rows are `lockForUpdate` during post

## Limitations (v0.1)

- No multi-currency / FX
- No cost-center dimensions
- No inventory valuation (see StockPulse)
- Trial balance lists raw debit/credit totals, not presentation-side nets

## Testing

```bash
composer install && composer test
```

## License

MIT
