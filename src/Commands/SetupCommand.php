<?php

namespace Localizy\LocalizyLaravel\Commands;

use Illuminate\Console\Command;
use Localizy\LocalizyLaravel\Localizy;

class SetupCommand extends Command
{
    public $signature = 'localizy:setup';

    public $description = 'My command';

    public function handle(): int
    {
        $response = app(Localizy::class)->makeSetupRequest();

        if ($response->hasErrors()) {
            $this->error($response->getMessage());

            return self::FAILURE;
        }

        $this->info($response->getMessage());

        return self::SUCCESS;
    }
}
