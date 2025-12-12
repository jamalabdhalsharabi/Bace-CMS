<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ModuleLoader;
use Illuminate\Console\Command;

class ModuleListCommand extends Command
{
    protected $signature = 'module:list 
                            {--enabled : Show only enabled modules}
                            {--disabled : Show only disabled modules}';

    protected $description = 'List all registered modules';

    public function handle(ModuleLoader $moduleLoader): int
    {
        $modules = $moduleLoader->all();

        if ($this->option('enabled')) {
            $modules = $moduleLoader->getEnabled();
        } elseif ($this->option('disabled')) {
            $modules = $moduleLoader->getDisabled();
        }

        if ($modules->isEmpty()) {
            $this->warn('No modules found.');
            return self::SUCCESS;
        }

        $this->info("Found {$modules->count()} module(s):\n");

        $headers = ['Name', 'Alias', 'Version', 'Priority', 'Status', 'Path'];
        $rows = [];

        foreach ($modules as $module) {
            $rows[] = [
                $module->getName(),
                $module->getAlias(),
                $module->getVersion(),
                $module->getPriority(),
                $module->isEnabled() ? '<fg=green>Enabled</>' : '<fg=red>Disabled</>',
                str_replace(base_path() . DIRECTORY_SEPARATOR, '', $module->getPath()),
            ];
        }

        $this->table($headers, $rows);

        return self::SUCCESS;
    }
}
