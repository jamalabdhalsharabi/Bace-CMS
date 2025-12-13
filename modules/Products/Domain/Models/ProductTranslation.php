<?php

declare(strict_types=1);

namespace Modules\Products\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProductTranslation
 *
 * Eloquent model representing a product translation
 * for multi-language support.
 *
 * @package Modules\Products\Domain\Models
 *
 * @property string $id
 * @property string $product_id
 * @property string $locale
 * @property string $name
 * @property string $slug
 * @property string|null $short_description
 * @property string|null $description
 * @property string|null $meta_title
 * @property string|null $meta_description
 *
 * @property-read Product $product
 */
class ProductTranslation extends Model
{
    use HasUuids;

    protected $table = 'product_translations';

    protected $fillable = [
        'product_id',
        'locale',
        'name',
        'slug',
        'short_description',
        'description',
        'meta_title',
        'meta_description',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
