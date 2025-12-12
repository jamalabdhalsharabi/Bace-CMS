<?php

declare(strict_types=1);

namespace Modules\Search\Engines;

use Illuminate\Support\Facades\Http;
use Modules\Search\Contracts\SearchEngineContract;

class MeilisearchEngine implements SearchEngineContract
{
    protected string $host;
    protected string $key;

    public function __construct()
    {
        $this->host = rtrim(config('search.meilisearch.host', 'http://localhost:7700'), '/');
        $this->key = config('search.meilisearch.key', '');
    }

    public function index(string $index, string $id, array $data): bool
    {
        $data['id'] = $id;

        $response = $this->request('POST', "/indexes/{$index}/documents", [$data]);

        return $response->successful();
    }

    public function delete(string $index, string $id): bool
    {
        $response = $this->request('DELETE', "/indexes/{$index}/documents/{$id}");

        return $response->successful();
    }

    public function search(string $index, array $options = []): array
    {
        $params = [
            'q' => $options['q'] ?? '',
            'limit' => $options['limit'] ?? config('search.per_page', 20),
            'offset' => $options['offset'] ?? 0,
        ];

        if (!empty($options['filter'])) {
            $params['filter'] = $options['filter'];
        }

        if (!empty($options['sort'])) {
            $params['sort'] = $options['sort'];
        }

        if (config('search.highlight.enabled', true)) {
            $params['attributesToHighlight'] = ['*'];
            $params['highlightPreTag'] = '<' . config('search.highlight.tag', 'mark') . '>';
            $params['highlightPostTag'] = '</' . config('search.highlight.tag', 'mark') . '>';
        }

        $response = $this->request('POST', "/indexes/{$index}/search", $params);

        if (!$response->successful()) {
            return ['hits' => [], 'total' => 0, 'error' => $response->body()];
        }

        $data = $response->json();

        return [
            'hits' => $data['hits'] ?? [],
            'total' => $data['estimatedTotalHits'] ?? 0,
            'query' => $params['q'],
            'processingTimeMs' => $data['processingTimeMs'] ?? 0,
        ];
    }

    public function createIndex(string $index, array $settings = []): bool
    {
        $response = $this->request('POST', '/indexes', [
            'uid' => $index,
            'primaryKey' => $settings['primaryKey'] ?? 'id',
        ]);

        if ($response->successful() && !empty($settings)) {
            $this->updateSettings($index, $settings);
        }

        return $response->successful();
    }

    public function deleteIndex(string $index): bool
    {
        $response = $this->request('DELETE', "/indexes/{$index}");

        return $response->successful();
    }

    public function updateSettings(string $index, array $settings): bool
    {
        $response = $this->request('PATCH', "/indexes/{$index}/settings", $settings);

        return $response->successful();
    }

    public function flush(string $index): bool
    {
        $response = $this->request('DELETE', "/indexes/{$index}/documents");

        return $response->successful();
    }

    protected function request(string $method, string $path, array $data = []): \Illuminate\Http\Client\Response
    {
        $request = Http::withHeaders([
            'Authorization' => "Bearer {$this->key}",
            'Content-Type' => 'application/json',
        ]);

        $url = $this->host . $path;

        return match (strtoupper($method)) {
            'GET' => $request->get($url, $data),
            'POST' => $request->post($url, $data),
            'PUT' => $request->put($url, $data),
            'PATCH' => $request->patch($url, $data),
            'DELETE' => $request->delete($url),
            default => $request->get($url),
        };
    }
}
