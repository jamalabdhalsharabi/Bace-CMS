<?php

declare(strict_types=1);

namespace Modules\Search\Domain\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SearchIndex Model - Stores full-text search index data.
 *
 * This model provides a database-based search index for
 * content that needs to be searchable.
 *
 * @property int $id Auto-increment primary key
 * @property string $index_name Index/collection name
 * @property string $document_id UUID of the indexed document
 * @property array $content Indexed content as JSON
 * @property string $searchable_text Full-text searchable content
 * @property \Carbon\Carbon $updated_at Last index update timestamp
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SearchIndex inIndex(string $indexName) Filter by index
 * @method static \Illuminate\Database\Eloquent\Builder|SearchIndex search(string $term) Full-text search
 * @method static \Illuminate\Database\Eloquent\Builder|SearchIndex newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SearchIndex newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SearchIndex query()
 */
class SearchIndex extends Model
{
    public $timestamps = false;

    protected $table = 'search_index';

    protected $fillable = [
        'index_name',
        'document_id',
        'content',
        'searchable_text',
        'updated_at',
    ];

    protected $casts = [
        'content' => 'array',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope to filter by index name.
     *
     * @param \Illuminate\Database\Eloquent\Builder<SearchIndex> $query
     * @param string $indexName The index name
     * @return \Illuminate\Database\Eloquent\Builder<SearchIndex>
     */
    public function scopeInIndex($query, string $indexName)
    {
        return $query->where('index_name', $indexName);
    }

    /**
     * Scope for full-text search.
     *
     * @param \Illuminate\Database\Eloquent\Builder<SearchIndex> $query
     * @param string $term Search term
     * @return \Illuminate\Database\Eloquent\Builder<SearchIndex>
     */
    public function scopeSearch($query, string $term)
    {
        return $query->whereRaw('MATCH(searchable_text) AGAINST(? IN BOOLEAN MODE)', [$term]);
    }

    /**
     * Index or update a document.
     *
     * @param string $indexName Index name
     * @param string $documentId Document UUID
     * @param array $content Content to index
     * @param string $searchableText Text for full-text search
     * @return self
     */
    public static function indexDocument(
        string $indexName,
        string $documentId,
        array $content,
        string $searchableText
    ): self {
        return static::updateOrCreate(
            ['index_name' => $indexName, 'document_id' => $documentId],
            ['content' => $content, 'searchable_text' => $searchableText, 'updated_at' => now()]
        );
    }
}
