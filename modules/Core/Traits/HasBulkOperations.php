<?php

declare(strict_types=1);

namespace Modules\Core\Traits;

use Illuminate\Support\Facades\DB;

trait HasBulkOperations
{
    public static function bulkPublish(array $ids): int
    {
        return static::whereIn('id', $ids)->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public static function bulkUnpublish(array $ids): int
    {
        return static::whereIn('id', $ids)->update([
            'status' => 'unpublished',
            'published_at' => null,
        ]);
    }

    public static function bulkArchive(array $ids): int
    {
        return static::whereIn('id', $ids)->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);
    }

    public static function bulkDelete(array $ids): int
    {
        return static::whereIn('id', $ids)->delete();
    }

    public static function bulkRestore(array $ids): int
    {
        return static::withTrashed()->whereIn('id', $ids)->restore();
    }

    public static function bulkForceDelete(array $ids): int
    {
        return static::withTrashed()->whereIn('id', $ids)->forceDelete();
    }

    public static function bulkUpdateField(array $ids, string $field, mixed $value): int
    {
        return static::whereIn('id', $ids)->update([$field => $value]);
    }

    public static function bulkAssignCategory(array $ids, string $categoryId, string $pivotTable = null): int
    {
        $pivotTable = $pivotTable ?? static::getCategoryPivotTable();
        $foreignKey = static::getForeignKeyName();
        
        $count = 0;
        foreach ($ids as $id) {
            DB::table($pivotTable)->insertOrIgnore([
                $foreignKey => $id,
                'term_id' => $categoryId,
            ]);
            $count++;
        }
        return $count;
    }

    public static function bulkRemoveCategory(array $ids, string $categoryId, string $pivotTable = null): int
    {
        $pivotTable = $pivotTable ?? static::getCategoryPivotTable();
        $foreignKey = static::getForeignKeyName();
        
        return DB::table($pivotTable)
            ->whereIn($foreignKey, $ids)
            ->where('term_id', $categoryId)
            ->delete();
    }

    protected static function getCategoryPivotTable(): string
    {
        return property_exists(static::class, 'categoryPivotTable') 
            ? static::$categoryPivotTable 
            : strtolower(class_basename(static::class)) . '_terms';
    }

    protected static function getForeignKeyName(): string
    {
        return strtolower(class_basename(static::class)) . '_id';
    }
}
