<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaticBlockTranslation extends Model
{
    use HasUuids;

    protected $table = 'static_block_translations';

    protected $fillable = ['static_block_id', 'locale', 'title', 'content'];

    public function block(): BelongsTo
    {
        return $this->belongsTo(StaticBlock::class, 'static_block_id');
    }
}
