<?php

declare(strict_types=1);

namespace Modules\Core\Domain\States;

use Illuminate\Database\Eloquent\Model;

/**
 * Base State Class for State Machine Pattern.
 *
 * Represents a state in a finite state machine.
 * Each concrete state defines allowed transitions.
 */
abstract class State
{
    /**
     * The model instance.
     *
     * @var Model
     */
    protected Model $model;

    /**
     * Create a new state instance.
     *
     * @param Model $model The model instance
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get the state value/name.
     *
     * @return string
     */
    abstract public static function getValue(): string;

    /**
     * Get the human-readable state label.
     *
     * @return string
     */
    abstract public static function getLabel(): string;

    /**
     * Get allowed transitions from this state.
     *
     * @return array<class-string<State>>
     */
    abstract public function allowedTransitions(): array;

    /**
     * Check if transition to a state is allowed.
     *
     * @param class-string<State> $state Target state class
     * @return bool
     */
    public function canTransitionTo(string $state): bool
    {
        return in_array($state, $this->allowedTransitions(), true);
    }

    /**
     * Transition to a new state.
     *
     * @param class-string<State> $state Target state class
     * @return State The new state instance
     * @throws \InvalidArgumentException If transition is not allowed
     */
    public function transitionTo(string $state): State
    {
        if (!$this->canTransitionTo($state)) {
            throw new \InvalidArgumentException(
                sprintf('Transition from %s to %s is not allowed', static::getValue(), $state::getValue())
            );
        }

        $this->model->update(['status' => $state::getValue()]);

        return new $state($this->model);
    }

    /**
     * Get state instance from model.
     *
     * @param Model $model The model instance
     * @param array<string, class-string<State>> $stateMap Map of value => state class
     * @return State
     */
    public static function fromModel(Model $model, array $stateMap): State
    {
        $value = $model->status ?? 'draft';
        $stateClass = $stateMap[$value] ?? array_values($stateMap)[0];

        return new $stateClass($model);
    }
}
