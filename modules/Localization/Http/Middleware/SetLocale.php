<?php

declare(strict_types=1);

namespace Modules\Localization\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Localization\Services\LocaleResolver;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function __construct(
        protected LocaleResolver $resolver
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolver->resolve($request);

        app()->setLocale($locale);

        session([config('localization.session_key', 'locale') => $locale]);

        $response = $next($request);

        if ($response instanceof Response) {
            $response->headers->set('Content-Language', $locale);
        }

        return $response;
    }
}
