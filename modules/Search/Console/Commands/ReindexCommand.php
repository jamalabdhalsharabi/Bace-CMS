<?php

declare(strict_types=1);

namespace Modules\Search\Console\Commands;

use Illuminate\Console\Command;
use Modules\Search\Contracts\SearchServiceContract;

class ReindexCommand extends Command
{
    protected $signature = 'search:reindex 
                            {index? : Specific index to reindex}
                            {--all : Reindex all indices}';

    protected $description = 'Reindex search data';

    public function handle(SearchServiceContract $searchService): int
    {
        $index = $this->argument('index');
        $all = $this->option('all');

        if (!$index && !$all) {
            $this->error('Please specify an index or use --all to reindex all indices.');
            return self::FAILURE;
        }

        if ($all) {
            $this->info('Reindexing all indices...');
            $results = $searchService->reindexAll();

            foreach ($results as $idx => $count) {
                $this->line("  - {$idx}: {$count} documents indexed");
            }

            $total = array_sum($results);
            $this->info("Completed! Total: {$total} documents indexed.");

            return self::SUCCESS;
        }

        $indices = config('search.indices', []);

        if (!isset($indices[$index])) {
            $this->error("Index '{$index}' not found.");
            $this->line('Available indices: ' . implode(', ', array_keys($indices)));

            return self::FAILURE;
        }

        $this->info("Reindexing '{$index}'...");
        $count = $searchService->reindex($index);
        $this->info("Completed! {$count} documents indexed.");

        return self::SUCCESS;
    }
}
