<?php

namespace Tipoff\TestSupport\Commands;

use Illuminate\Console\Command;

class TestSupportCommand extends Command
{
    public $signature = 'test-support';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
