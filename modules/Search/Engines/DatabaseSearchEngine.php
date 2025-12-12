<?php

declare(strict_types=1);

namespace Modules\Search\Engines;

use Illuminate\Support\Facades\DB;
use Modules\Search\Contracts\SearchEngineContract;

class DatabaseSearchEngine implements SearchEngineContract
{
    protected array $indices = [];

    public function index(string $index, string $id, array $data): bool
    {
        DB::table('search_index')->updateOrInsert(
            ['index_name' => $index, 'document_id' => $id],
            [
                'content' => json_encode($data),
                'searchable_text' => $this->extractSearchableText($data),
                'updated_at' => now(),
            ]
        );

        return true;
    }

    public function delete(string $index, string $id): bool
    {
        return DB::table('search_index')
            ->where('index_name', $index)
            ->where('document_id', $id)
            ->delete() > 0;
    }

    public function search(string $index, array $options = []): array
    {
        $query = $options['q'] ?? '';
        $limit = $options['limit'] ?? config('search.per_page', 20);
        $offset = $options['offset'] ?? 0;

        if (strlen($query) < config('search.min_query_length', 2)) {
            return ['hits' => [], 'total' => 0];
        }

        $results = DB::table('search_index')
            ->where('index_name', $index)
            ->where('searchable_text', 'LIKE', "%{$query}%")
            ->orderByRaw("CASE WHEN searchable_text LIKE ? THEN 1 ELSE 2 END", ["{$query}%"])
            ->offset($offset)
            ->limit($limit)
            ->get();

        $total = DB::table('search_index')
            ->where('index_name', $index)
            ->where('searchable_text', 'LIKE', "%{$query}%")
            ->count();

        $hits = $results->map(function ($row) use ($query) {
            $data = json_decode($row->content, true);
            $data['_id'] = $row->document_id;
            $data['_score'] = $this->calculateScore($row->searchable_text, $query);

            return $data;
        })->toArray();

        return [
            'hits' => $hits,
            'total' => $total,
            'query' => $query,
        ];
    }

    public function createIndex(string $index, array $settings = []): bool
    {
        return true;
    }

    public function deleteIndex(string $index): bool
    {
        return DB::table('search_index')->where('index_name', $index)->delete() >= 0;
    }

    public function updateSettings(string $index, array $settings): bool
    {
        return true;
    }

    public function flush(string $index): bool
    {
        return $this->deleteIndex($index);
    }

    protected function extractSearchableText(array $data): string
    {
        $text = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $text[] = strip_tags($value);
            } elseif (is_array($value)) {
                $text[] = $this->extractSearchableText($value);
            }
        }

        return implode(' ', $text);
    }

    protected function calculateScore(string $text, string $query): float
    {
        $text = strtolower($text);
        $query = strtolower($query);

        if (str_starts_with($text, $query)) {
            return 1.0;
        }

        $occurrences = substr_count($text, $query);

        return min(0.9, $occurrences * 0.1);
    }
}
