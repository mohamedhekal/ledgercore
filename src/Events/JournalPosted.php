<?php

declare(strict_types=1);

namespace Hekal\LedgerCore\Events;

use Hekal\LedgerCore\Models\Journal;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class JournalPosted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Journal $journal,
    ) {}
}
