<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ModuleLoader;
use Illuminate\Console\Command;

class ModuleEnableCommand extends Command
{
    protected $signature = 'module:enable {name : The module alias to enable}';

    protected $description = 'Enable a module';

    public function handle(ModuleLoader $moduleLoader): int
    {
        $name = strtolower($this->argument('name'));

        if (!$moduleLoader->has($name)) {
            $this->error("Module [{$name}] not found!");
            return self::FAILURE;
        }

        if ($moduleLoader->enable($name)) {
            $this->info("Module [{$name}] enabled successfully!");
            $this->warn('Run "php artisan config:clear" to apply changes.');
            return self::SUCCESS;
        }

        $this->error("Failed to enable module [{$name}].");
        return self::FAILURE;
    }
}
