<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProfileLoader;
use Illuminate\Console\Command;

class ProfileApplyCommand extends Command
{
    protected $signature = 'profile:apply 
                            {name : The profile name to apply}
                            {--list : List available profiles}';

    protected $description = 'Apply a configuration profile';

    public function handle(ProfileLoader $profileLoader): int
    {
        if ($this->option('list')) {
            return $this->listProfiles($profileLoader);
        }

        $name = $this->argument('name');

        try {
            $this->info("Applying profile: {$name}");

            $profileLoader->apply($name);

            $this->info("Profile [{$name}] applied successfully!");
            $this->newLine();
            $this->warn('Run the following commands to apply changes:');
            $this->line('  php artisan config:clear');
            $this->line('  php artisan cache:clear');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    protected function listProfiles(ProfileLoader $profileLoader): int
    {
        $profiles = $profileLoader->getAvailable();

        if (empty($profiles)) {
            $this->warn('No profiles found.');
            return self::SUCCESS;
        }

        $this->info("Available profiles:\n");

        $rows = [];
        foreach ($profiles as $name => $info) {
            $rows[] = [
                $name,
                $info['description'] ?? '',
                $info['version'] ?? '1.0.0',
            ];
        }

        $this->table(['Name', 'Description', 'Version'], $rows);

        return self::SUCCESS;
    }
}
