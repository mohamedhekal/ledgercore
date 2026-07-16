<?php

declare(strict_types=1);

return [

    'currency' => env('LEDGERCORE_CURRENCY', 'USD'),

    'tables' => [
        'accounts' => 'lc_accounts',
        'journals' => 'lc_journals',
        'lines' => 'lc_journal_lines',
        'balances' => 'lc_balances',
        'periods' => 'lc_fiscal_periods',
    ],

];
