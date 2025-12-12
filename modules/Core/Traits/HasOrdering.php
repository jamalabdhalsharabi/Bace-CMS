<?php

declare(strict_types=1);

namespace Modules\Core\Traits;

trait HasOrdering
{
    /**
     * Boot the trait.
     */
    public static function bootHasOrdering(): void
    {
        static::creating(function ($model) {
            if (is_null($model->ordering)) {
                $model->ordering = static::getNextOrder($model);
            }
        });
    }

    /**
     * Get the next order value.
     */
    protected static function getNextOrder($model): int
    {
        $query = static::query();

        // If model has a grouping column (e.g., parent_id, category_id)
        if (property_exists($model, 'orderingGroup') && $model->orderingGroup) {
            $groupColumn = $model->orderingGroup;
            $query->where($groupColumn, $model->{$groupColumn});
        }

        return (int) $query->max('ordering') + 1;
    }

    /**
     * Move to specific position.
     */
    public function moveTo(int $position): static
    {
        $this->ordering = $position;
        $this->save();
        $this->reorderSiblings();
        return $this;
    }

    /**
     * Move up in order.
     */
    public function moveUp(): static
    {
        if ($this->ordering > 1) {
            $previous = $this->getPreviousSibling();
            if ($previous) {
                $previousOrder = $previous->ordering;
                $previous->ordering = $this->ordering;
                $this->ordering = $previousOrder;
                $previous->save();
                $this->save();
            }
        }
        return $this;
    }

    /**
     * Move down in order.
     */
    public function moveDown(): static
    {
        $next = $this->getNextSibling();
        if ($next) {
            $nextOrder = $next->ordering;
            $next->ordering = $this->ordering;
            $this->ordering = $nextOrder;
            $next->save();
            $this->save();
        }
        return $this;
    }

    /**
     * Move to first position.
     */
    public function moveToFirst(): static
    {
        return $this->moveTo(1);
    }

    /**
     * Move to last position.
     */
    public function moveToLast(): static
    {
        $lastOrder = $this->getSiblingsQuery()->max('ordering') ?? 0;
        return $this->moveTo($lastOrder + 1);
    }

    /**
     * Get previous sibling.
     */
    protected function getPreviousSibling(): ?static
    {
        return $this->getSiblingsQuery()
            ->where('ordering', '<', $this->ordering)
            ->orderByDesc('ordering')
            ->first();
    }

    /**
     * Get next sibling.
     */
    protected function getNextSibling(): ?static
    {
        return $this->getSiblingsQuery()
            ->where('ordering', '>', $this->ordering)
            ->orderBy('ordering')
            ->first();
    }

    /**
     * Get siblings query.
     */
    protected function getSiblingsQuery()
    {
        $query = static::where('id', '!=', $this->id);

        if (property_exists($this, 'orderingGroup') && $this->orderingGroup) {
            $groupColumn = $this->orderingGroup;
            $query->where($groupColumn, $this->{$groupColumn});
        }

        return $query;
    }

    /**
     * Reorder siblings after position change.
     */
    protected function reorderSiblings(): void
    {
        $siblings = $this->getSiblingsQuery()
            ->orderBy('ordering')
            ->get();

        $order = 1;
        foreach ($siblings as $sibling) {
            if ($order === $this->ordering) {
                $order++;
            }
            if ($sibling->ordering !== $order) {
                $sibling->ordering = $order;
                $sibling->saveQuietly();
            }
            $order++;
        }
    }

    /**
     * Scope: Order by position.
     */
    public function scopeOrdered($query, string $direction = 'asc')
    {
        return $query->orderBy('ordering', $direction);
    }
}
