<?php

declare(strict_types=1);

namespace Modules\Core\Domain\DTO;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use ReflectionClass;
use ReflectionProperty;

/**
 * Base Data Transfer Object.
 *
 * Provides a structured way to transfer data between layers.
 * Immutable by design - properties should be readonly.
 *
 * @implements Arrayable<string, mixed>
 */
abstract class DataTransferObject implements Arrayable
{
    /**
     * Create a new DTO instance from an array.
     *
     * @param array<string, mixed> $data The source data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        return new static(...static::mapToConstructor($data));
    }

    /**
     * Create a new DTO instance from a Request.
     *
     * @param Request $request The HTTP request
     * @return static
     */
    public static function fromRequest(Request $request): static
    {
        return static::fromArray($request->validated());
    }

    /**
     * Convert DTO to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        $data = [];

        foreach ($properties as $property) {
            $value = $property->getValue($this);
            $data[$property->getName()] = $value instanceof self ? $value->toArray() : $value;
        }

        return $data;
    }

    /**
     * Get only non-null values as array.
     *
     * @return array<string, mixed>
     */
    public function toArrayWithoutNulls(): array
    {
        return array_filter($this->toArray(), fn ($value) => $value !== null);
    }

    /**
     * Map array data to constructor parameters.
     *
     * @param array<string, mixed> $data The source data
     * @return array<string, mixed>
     */
    protected static function mapToConstructor(array $data): array
    {
        $reflection = new ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return [];
        }

        $args = [];
        foreach ($constructor->getParameters() as $param) {
            $name = $param->getName();
            $args[$name] = $data[$name] ?? ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null);
        }

        return $args;
    }
}
