<?php

declare(strict_types=1);

namespace Modules\Core\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Trait HasImportExport
 * 
 * Provides import and export functionality for Eloquent models.
 * Supports array and CSV formats with translation handling.
 * 
 * @package Modules\Core\Traits
 */
trait HasImportExport
{
    /**
     * Export models to an array format.
     *
     * @param array $filters Optional filters (ids, status)
     * @return array Exported data
     */
    public static function exportToArray(array $filters = []): array
    {
        $query = static::query();
        
        if (!empty($filters['ids'])) {
            $query->whereIn('id', $filters['ids']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get()->map(fn($item) => $item->toExportArray())->toArray();
    }

    public function toExportArray(): array
    {
        $data = $this->toArray();
        
        // Remove system fields
        unset($data['id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);
        
        // Include translations if available
        if (method_exists($this, 'translations')) {
            $data['translations'] = $this->translations->keyBy('locale')->map(fn($t) => 
                collect($t->toArray())->except(['id', 'created_at', 'updated_at', $this->getForeignKey()])->toArray()
            )->toArray();
        }
        
        return $data;
    }

    public static function importFromArray(array $data, string $mode = 'create'): array
    {
        $results = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        DB::beginTransaction();
        try {
            foreach ($data as $index => $item) {
                try {
                    $existing = null;
                    if (!empty($item['slug'])) {
                        $existing = static::where('slug', $item['slug'])->first();
                    }

                    if ($existing) {
                        if ($mode === 'update' || $mode === 'upsert') {
                            $existing->fill($item);
                            $existing->save();
                            
                            if (!empty($item['translations']) && method_exists($existing, 'translations')) {
                                foreach ($item['translations'] as $locale => $trans) {
                                    $existing->translations()->updateOrCreate(['locale' => $locale], $trans);
                                }
                            }
                            
                            $results['updated']++;
                        } else {
                            $results['skipped']++;
                        }
                    } else {
                        $model = static::create($item);
                        
                        if (!empty($item['translations']) && method_exists($model, 'translations')) {
                            foreach ($item['translations'] as $locale => $trans) {
                                $model->translations()->create(['locale' => $locale, ...$trans]);
                            }
                        }
                        
                        $results['created']++;
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = ['index' => $index, 'error' => $e->getMessage()];
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $results['errors'][] = ['error' => $e->getMessage()];
        }

        return $results;
    }

    public static function exportToCsv(array $filters = []): string
    {
        $data = static::exportToArray($filters);
        if (empty($data)) return '';

        $headers = array_keys($data[0]);
        $csv = implode(',', $headers) . "\n";
        
        foreach ($data as $row) {
            $csv .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', (string)$v) . '"', $row)) . "\n";
        }
        
        return $csv;
    }
}
