<?php

declare(strict_types=1);

namespace Modules\Forms\Services;

use Illuminate\Support\Facades\Cache;

class SpamDetector
{
    protected array $spamKeywords = [
        'viagra', 'casino', 'lottery', 'winner', 'bitcoin',
        'cryptocurrency', 'investment opportunity', 'make money fast',
    ];

    public function isSpam(array $data, array $meta = []): bool
    {
        if (!config('forms.spam_detection.enabled', true)) {
            return false;
        }

        if ($this->checkHoneypot($data)) {
            return true;
        }

        if ($this->checkRateLimit($meta['ip'] ?? request()->ip())) {
            return true;
        }

        if ($this->checkSpamKeywords($data)) {
            return true;
        }

        if ($this->checkLinks($data)) {
            return true;
        }

        return false;
    }

    protected function checkHoneypot(array $data): bool
    {
        if (!config('forms.spam_detection.honeypot', true)) {
            return false;
        }

        return !empty($data['_honeypot'] ?? $data['website'] ?? null);
    }

    protected function checkRateLimit(string $ip): bool
    {
        $limit = config('forms.spam_detection.rate_limit', 5);
        $period = config('forms.spam_detection.rate_limit_period', 60);

        $key = "form_submission_{$ip}";
        $count = Cache::get($key, 0);

        if ($count >= $limit) {
            return true;
        }

        Cache::put($key, $count + 1, $period);

        return false;
    }

    protected function checkSpamKeywords(array $data): bool
    {
        $content = strtolower(implode(' ', array_values(array_filter($data, 'is_string'))));

        foreach ($this->spamKeywords as $keyword) {
            if (str_contains($content, $keyword)) {
                return true;
            }
        }

        return false;
    }

    protected function checkLinks(array $data): bool
    {
        $content = implode(' ', array_values(array_filter($data, 'is_string')));
        $linkCount = preg_match_all('/https?:\/\//', $content);

        return $linkCount > 3;
    }
}
