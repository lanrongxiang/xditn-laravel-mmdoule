<?php

namespace Xditn\Commands;

use Illuminate\Console\Command;
use Xditn\MModule;

class VersionCommand extends Command
{
    protected $signature = 'xditn:version';

    protected $description = '显示xditn 版本';

    public function handle(): void
    {
        $this->info(MModule::VERSION);
    }
}
