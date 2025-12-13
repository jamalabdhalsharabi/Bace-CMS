<?php

declare(strict_types=1);

namespace Modules\Content\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Translation Data Transfer Object.
 *
 * Immutable data structure for article translation data.
 */
final class TranslationData extends DataTransferObject
{
    /**
     * @param string $title Article title
     * @param string|null $slug URL slug (auto-generated if null)
     * @param string|null $excerpt Short excerpt
     * @param string|null $content Full content
     * @param string|null $meta_title SEO meta title
     * @param string|null $meta_description SEO meta description
     * @param string|null $meta_keywords SEO keywords
     */
    public function __construct(
        public readonly string $title,
        public readonly ?string $slug = null,
        public readonly ?string $excerpt = null,
        public readonly ?string $content = null,
        public readonly ?string $meta_title = null,
        public readonly ?string $meta_description = null,
        public readonly ?string $meta_keywords = null,
    ) {}
}
