<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ModuleLoader;
use Illuminate\Console\Command;

class ModuleDisableCommand extends Command
{
    protected $signature = 'module:disable {name : The module alias to disable}';

    protected $description = 'Disable a module';

    public function handle(ModuleLoader $moduleLoader): int
    {
        $name = strtolower($this->argument('name'));

        if (!$moduleLoader->has($name)) {
            $this->error("Module [{$name}] not found!");
            return self::FAILURE;
        }

        // Check if it's a core module
        $coreModules = config('modules.core_modules', []);
        if (in_array($name, $coreModules, true)) {
            $this->error("Module [{$name}] is a core module and cannot be disabled!");
            return self::FAILURE;
        }

        if ($moduleLoader->disable($name)) {
            $this->info("Module [{$name}] disabled successfully!");
            $this->warn('Run "php artisan config:clear" to apply changes.');
            return self::SUCCESS;
        }

        $this->error("Failed to disable module [{$name}].");
        return self::FAILURE;
    }
}
