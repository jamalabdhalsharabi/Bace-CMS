<?php

declare(strict_types=1);

namespace Modules\Localization\Services;

use Illuminate\Http\Request;
use Modules\Localization\Domain\Models\Language;

class LocaleResolver
{
    protected array $supportedLocales = [];

    public function __construct()
    {
        $this->supportedLocales = Language::getActiveCodes();

        if (empty($this->supportedLocales)) {
            $this->supportedLocales = config('localization.supported_locales', ['en']);
        }
    }

    public function resolve(Request $request): string
    {
        $detectors = config('localization.detect_from', []);

        foreach ($detectors as $detector) {
            $locale = match ($detector) {
                'url_segment' => $this->fromUrlSegment($request),
                'query_param' => $this->fromQueryParam($request),
                'header' => $this->fromHeader($request),
                'session' => $this->fromSession($request),
                'cookie' => $this->fromCookie($request),
                'user_preference' => $this->fromUserPreference($request),
                default => null,
            };

            if ($locale && $this->isSupported($locale)) {
                return $locale;
            }
        }

        return $this->getDefault();
    }

    protected function fromUrlSegment(Request $request): ?string
    {
        $position = config('localization.url_segment_position', 1);
        $segments = $request->segments();

        return $segments[$position - 1] ?? null;
    }

    protected function fromQueryParam(Request $request): ?string
    {
        $param = config('localization.query_param', 'lang');

        return $request->query($param);
    }

    protected function fromHeader(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');

        if (!$acceptLanguage) {
            return null;
        }

        $locales = explode(',', $acceptLanguage);

        foreach ($locales as $locale) {
            $locale = trim(explode(';', $locale)[0]);
            $locale = substr($locale, 0, 2);

            if ($this->isSupported($locale)) {
                return $locale;
            }
        }

        return null;
    }

    protected function fromSession(Request $request): ?string
    {
        $key = config('localization.session_key', 'locale');

        return session($key);
    }

    protected function fromCookie(Request $request): ?string
    {
        $name = config('localization.cookie_name', 'locale');

        return $request->cookie($name);
    }

    protected function fromUserPreference(Request $request): ?string
    {
        $user = $request->user();

        if (!$user) {
            return null;
        }

        return $user->profile?->locale ?? $user->locale ?? null;
    }

    public function isSupported(string $locale): bool
    {
        return in_array($locale, $this->supportedLocales, true);
    }

    public function getDefault(): string
    {
        $default = Language::getDefault();

        return $default?->code ?? config('localization.default_locale', 'en');
    }

    public function getSupportedLocales(): array
    {
        return $this->supportedLocales;
    }

    public function isRtl(string $locale): bool
    {
        $rtlLocales = config('localization.rtl_locales', ['ar', 'he', 'fa', 'ur']);

        return in_array($locale, $rtlLocales, true);
    }
}
