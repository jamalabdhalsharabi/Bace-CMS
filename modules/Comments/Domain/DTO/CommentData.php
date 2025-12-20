<?php

declare(strict_types=1);

namespace Modules\Comments\Domain\DTO;

use Modules\Core\Domain\DTO\DataTransferObject;

/**
 * Comment Data Transfer Object.
 *
 * Immutable data structure for transferring comment data between layers.
 * Encapsulates all necessary information for creating or updating comments,
 * supporting both authenticated user comments and guest comments.
 *
 * This DTO is used to:
 * - Transfer validated data from HTTP requests to Actions
 * - Ensure type safety across application layers
 * - Maintain immutability of data during processing
 * - Provide clear contract for comment creation
 *
 * @package Modules\Comments\Domain\DTO
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class CommentData extends DataTransferObject
{
    /**
     * Create a new CommentData instance.
     *
     * @param string $commentable_type The fully qualified class name of the commentable entity
     * @param string $commentable_id The UUID of the commentable entity
     * @param string $content The comment text content
     * @param string|null $parent_id Optional parent comment UUID for replies
     * @param string|null $user_id Optional authenticated user UUID
     * @param string|null $author_name Optional guest author name (required if user_id is null)
     * @param string|null $author_email Optional guest author email (required if user_id is null)
     */
    public function __construct(
        public readonly string $commentable_type,
        public readonly string $commentable_id,
        public readonly string $content,
        public readonly ?string $parent_id = null,
        public readonly ?string $user_id = null,
        public readonly ?string $author_name = null,
        public readonly ?string $author_email = null,
    ) {}
}
