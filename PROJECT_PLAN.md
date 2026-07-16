# LedgerCore — Project Plan

## Name

**LedgerCore** (`hekal/ledgercore`)  
Alternatives: `journalkit`, `doubleentry`

## Vision

Correct-by-construction double-entry primitives for Laravel ERP/fintech: chart of accounts, balanced journals, immutable postings, balance projections, and fiscal period locks.

## v0.1 scope

- Account types + normal balance
- Post journal (all-or-nothing, balanced only)
- Reverse journal (compensating entry)
- Account balances (lockForUpdate)
- Fiscal period open/close enforcement
- Trial balance query
- Amounts in minor units (integers) — single currency
- Pest tests for imbalance rejection + period locks

## Out of v0.1

- Multi-currency / FX
- Dimensions (cost centers)
- Report exports / PDF
- Inventory valuation layers (belongs in StockPulse)
