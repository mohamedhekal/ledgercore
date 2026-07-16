# Architecture

## Invariants

1. Every journal: Σ debit = Σ credit > 0
2. Each line: exactly one of debit/credit is positive
3. Postings never update in place — reverse creates a compensating journal
4. Period must exist and be open for `posted_on`
5. Balance updates happen in the same DB transaction as journal insert

## Concurrency

Account ids are sorted before `lockForUpdate` to reduce deadlock risk. Balance rows are locked per affected account.

## Amounts

Integer minor units avoid float drift. Host apps convert for display.

## Trade-offs

- No ORM soft-deletes on lines/journals (immutability)
- Trial balance uses cumulative debit/credit totals (always balance if only this package posts)
- Multi-currency deferred to keep the correctness core small
