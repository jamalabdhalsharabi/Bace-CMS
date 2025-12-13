<?php

declare(strict_types=1);

namespace Modules\Seo\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Seo\Domain\Models\PageView;

final class LogPageViewAction extends Action
{
    public function execute(array $data): PageView
    {
        return PageView::create([
            'url' => $data['url'],
            'user_id' => $data['user_id'] ?? $this->userId(),
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
            'referrer' => $data['referrer'] ?? request()->header('referer'),
            'viewed_at' => now(),
        ]);
    }
}
