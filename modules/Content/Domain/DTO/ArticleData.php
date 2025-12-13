<?php

declare(strict_types=1);

namespace Modules\Content\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Article Data Transfer Object.
 *
 * Immutable data structure for transferring article data between layers.
 */
final class ArticleData extends DataTransferObject
{
    /**
     * @param string|null $author_id Article author UUID
     * @param string|null $featured_image_id Featured image UUID
     * @param string $type Article type (post, news, tutorial)
     * @param string $status Publication status
     * @param bool $is_featured Whether article is featured
     * @param bool $allow_comments Whether comments are allowed
     * @param array<string, TranslationData> $translations Translations keyed by locale
     * @param array<string>|null $category_ids Category UUIDs
     * @param array<string>|null $tag_ids Tag UUIDs
     * @param array<string, mixed>|null $meta Additional metadata
     */
    public function __construct(
        public readonly ?string $author_id = null,
        public readonly ?string $featured_image_id = null,
        public readonly string $type = 'post',
        public readonly string $status = 'draft',
        public readonly bool $is_featured = false,
        public readonly bool $allow_comments = true,
        public readonly array $translations = [],
        public readonly ?array $category_ids = null,
        public readonly ?array $tag_ids = null,
        public readonly ?array $meta = null,
    ) {}
}
