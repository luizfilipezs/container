<?php

namespace Luizfilipezs\Container;

use Luizfilipezs\Container\Attributes\{LazyGhost, Singleton};
use Luizfilipezs\Container\Exceptions\ContainerException;
use ReflectionClass;

class Container
{
    /**
     * Class definitions.
     *
     * @var array<string,class-string,callable,object>
     */
    private array $definitions = [];

    /**
     * @template T
     * @param class-string<T> $className
     * @return T
     */
    public function get(string $className): mixed
    {
        if ($this->has($className)) {
            return $this->getFromDefinition($className);
        }

        $reflectionClass = new ReflectionClass($className);
        $instance = $this->isLazy($reflectionClass)
            ? $this->instantiateLazy($reflectionClass)
            : $this->instantiate($reflectionClass);

        if ($this->isSingleton($reflectionClass)) {
            $this->set($className, $instance);
        }

        return $instance;
    }

    public function set(string $className, mixed $definition): void
    {
        $this->definitions[$className] = $definition;
    }

    public function has(string $className): bool
    {
        return array_key_exists($className, $this->definitions);
    }

    public function remove(string $className): void
    {
        unset($this->definitions[$className]);
    }

    private function getFromDefinition(string $className): mixed
    {
        $definition = $this->definitions[$className];

        if (is_string($definition)) {
            if (!class_exists($definition)) {
                throw new ContainerException(
                    "Container definition for {$className} is a string, but it is not a valid class name.",
                );
            }

            return $this->get($definition);
        }

        if (is_callable($definition)) {
            return $definition();
        }

        if (is_object($definition)) {
            if (get_class($definition) !== $className) {
                throw new ContainerException(
                    "Container definition for $className is an object, but it is not an instance of $className.",
                );
            }

            return $definition;
        }

        throw new ContainerException(
            "Container definition for $className is not a valid definition.",
        );
    }

    private function isLazy(ReflectionClass $reflectionClass): bool
    {
        return $this->hasAttribute($reflectionClass, LazyGhost::class);
    }

    private function isSingleton(ReflectionClass $reflectionClass): bool
    {
        return $this->hasAttribute($reflectionClass, Singleton::class);
    }

    private function hasAttribute(ReflectionClass $reflectionClass, string $attributeClass): bool
    {
        $attributes = $reflectionClass->getAttributes($attributeClass);

        return count($attributes) > 0;
    }

    private function instantiate(ReflectionClass $reflectionClass)
    {
        return $reflectionClass->newInstanceArgs($this->createConstructorArgs($reflectionClass));
    }

    private function instantiateLazy(ReflectionClass $reflectionClass)
    {
        return $reflectionClass->newLazyGhost(
            fn($instance) => $instance->__construct(
                ...$this->createConstructorArgs($reflectionClass),
            ),
        );
    }

    private function createConstructorArgs(ReflectionClass $reflectionClass): array
    {
        $constructReflection = $reflectionClass->getConstructor();

        if ($constructReflection === null) {
            return [];
        }

        $constructParams = $constructReflection->getParameters();
        $arguments = [];

        foreach ($constructParams as $param) {
            $paramName = $param->getType()->getName();

            if (in_array($paramName, ['self', 'parent', 'static'])) {
                throw new ContainerException(
                    "Container cannot inject {$paramName}. It only works with different classes.",
                );
            }

            if (!class_exists($paramName)) {
                throw new ContainerException(
                    "Container cannot inject {$paramName}. It is not a class or does not exist.",
                );
            }

            $arguments[] = $this->get($paramName);
        }

        return $arguments;
    }
}
