<?php

namespace Localizy\LocalizyLaravel\Commands;

use Illuminate\Console\Command;

class SyncCommand extends Command
{
    public $signature = 'localizy:sync';

    public $description = 'My command';

    public function handle(): int
    {
        $this->line('TODO');

        return self::SUCCESS;
    }
}
