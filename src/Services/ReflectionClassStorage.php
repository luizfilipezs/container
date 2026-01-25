<?php

declare(strict_types=1);

namespace Luizfilipezs\Container\Services;

use Luizfilipezs\Container\Interfaces\ReflectionClassStorageInterface;

final class ReflectionClassStorage implements ReflectionClassStorageInterface
{
    private array $reflectionClasses = [];

    public function create(string $className): \ReflectionClass
    {
        if ($this->has($className)) {
            throw new \InvalidArgumentException("{$className} has already been added.");
        }

        $reflectionClass = new \ReflectionClass($className);
        $this->reflectionClasses[$className] = $reflectionClass;

        return $reflectionClass;
    }

    public function get(string $className): ?\ReflectionClass
    {
        return $this->reflectionClasses[$className];
    }

    public function getOrCreate(string $className): \ReflectionClass
    {
        return $this->has($className) ?
            $this->get($className) :
            $this->create($className);
    }

    public function has(string $className): bool
    {
        return isset($this->reflectionClasses[$className]);
    }
}
