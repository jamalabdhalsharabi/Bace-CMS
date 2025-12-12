<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ModuleLoader;
use Illuminate\Console\Command;

class ModuleMigrateCommand extends Command
{
    protected $signature = 'module:migrate 
                            {name? : The module alias to migrate (all if not specified)}
                            {--rollback : Rollback the last migration}
                            {--refresh : Refresh all migrations}
                            {--seed : Seed the database after migrating}
                            {--force : Force the operation in production}';

    protected $description = 'Run migrations for a module or all modules';

    public function handle(ModuleLoader $moduleLoader): int
    {
        $name = $this->argument('name');

        if ($name) {
            return $this->migrateModule($moduleLoader, strtolower($name));
        }

        return $this->migrateAllModules($moduleLoader);
    }

    protected function migrateModule(ModuleLoader $moduleLoader, string $name): int
    {
        if (!$moduleLoader->has($name)) {
            $this->error("Module [{$name}] not found!");
            return self::FAILURE;
        }

        $module = $moduleLoader->get($name);
        $migrationPath = $module->getPath() . '/Database/Migrations';

        $this->info("Migrating module: {$module->getName()}");

        return $this->runMigration($migrationPath);
    }

    protected function migrateAllModules(ModuleLoader $moduleLoader): int
    {
        $modules = $moduleLoader->getEnabled();

        if ($modules->isEmpty()) {
            $this->warn('No enabled modules found.');
            return self::SUCCESS;
        }

        $this->info("Migrating {$modules->count()} module(s)...\n");

        foreach ($modules as $module) {
            $migrationPath = $module->getPath() . '/Database/Migrations';
            
            $this->line("→ {$module->getName()}");
            $this->runMigration($migrationPath);
        }

        $this->newLine();
        $this->info('All module migrations completed!');

        return self::SUCCESS;
    }

    protected function runMigration(string $path): int
    {
        $options = [
            '--path' => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path),
            '--realpath' => true,
        ];

        if ($this->option('force')) {
            $options['--force'] = true;
        }

        if ($this->option('rollback')) {
            return $this->call('migrate:rollback', $options);
        }

        if ($this->option('refresh')) {
            return $this->call('migrate:refresh', $options);
        }

        $result = $this->call('migrate', $options);

        if ($this->option('seed')) {
            $this->call('db:seed');
        }

        return $result;
    }
}
