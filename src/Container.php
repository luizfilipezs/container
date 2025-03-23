<?php

namespace Luizfilipezs\Container;

use Luizfilipezs\Container\Attributes\{Inject, Lazy, Singleton};
use Luizfilipezs\Container\Exceptions\ContainerException;
use ReflectionClass;
use ReflectionParameter;

class Container
{
    /**
     * Class definitions.
     *
     * @var array<string,class-string,callable,object>
     */
    private array $definitions = [];

    /**
     * Value definitions.
     *
     * @var array<string,mixed>
     */
    private array $valueDefinitions = [];

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

    public function getValue(string $identifier): mixed
    {
        return $this->valueDefinitions[$identifier] ?? null;
    }

    public function setValue(string $identifier, mixed $value): void
    {
        $this->valueDefinitions[$identifier] = $value;
    }

    public function hasValue(string $identifier): bool
    {
        return array_key_exists($identifier, $this->valueDefinitions);
    }

    public function removeValue(string $identifier): void
    {
        unset($this->valueDefinitions[$identifier]);
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
        return $this->hasAttribute($reflectionClass, Lazy::class);
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
            $paramType = $param->getType()->getName();

            if (in_array($paramType, ['self', 'parent', 'static'])) {
                throw new ContainerException(
                    "Container cannot inject {$paramType}. A dependency cannot refer to the same class.",
                );
            }

            $injectedValue = $this->getParamValueFromDefinition($param);

            if ($injectedValue !== null) {
                $arguments[] = $injectedValue;
                continue;
            }

            if (!class_exists($paramType)) {
                throw new ContainerException(
                    "Container cannot inject {$paramType}. It is not a class or does not exist.",
                );
            }

            $arguments[] = $this->get($paramType);
        }

        return $arguments;
    }

    private function getParamValueFromDefinition(ReflectionParameter $param): mixed
    {
        $injectAttribute = $param->getAttributes(Inject::class)[0]?->newInstance();

        if ($injectAttribute === null) {
            return null;
        }

        $value = $this->getValue($injectAttribute->identifier);

        if ($value === null) {
            throw new ContainerException(
                "Container cannot inject {$injectAttribute->identifier}. It is not defined.",
            );
        }

        if ($param->getType()->getName() !== gettype($value)) {
            throw new ContainerException(
                "Container cannot inject {$injectAttribute->identifier}. It is not the same type as the parameter.",
            );
        }

        return $value;
    }
}
