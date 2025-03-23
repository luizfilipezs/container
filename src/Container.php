<?php

namespace Luizfilipezs\Container;

use Luizfilipezs\Container\Attributes\{Inject, Lazy, Singleton};
use Luizfilipezs\Container\Exceptions\ContainerException;
use ReflectionClass;
use ReflectionParameter;

/**
 * Dependency injection container.
 */
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
     * Gets a class instance.
     *
     * @template T
     *
     * @param class-string<T> $className Class name.
     *
     * @return T Class instance.
     *
     * @throws ContainerException If instance cannot be created.
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

    /**
     * Sets a class definition.
     *
     * @template T
     *
     * @param class-string<T> $className Class name.
     * @param T|class-string<T>|callable|callable(): T $definition Class definition.
     */
    public function set(string $className, mixed $definition): void
    {
        $this->definitions[$className] = $definition;
    }

    /**
     * Checks if a class is defined.
     *
     * @param class-string $className Class name.
     *
     * @return bool If class is defined.
     */
    public function has(string $className): bool
    {
        return array_key_exists($className, $this->definitions);
    }

    /**
     * Removes a class definition.
     *
     * @param class-string $className Class name.
     */
    public function remove(string $className): void
    {
        unset($this->definitions[$className]);
    }

    /**
     * Gets a value definition.
     *
     * @param string $identifier Value identifier.
     *
     * @return mixed Value definition.
     */
    public function getValue(string $identifier): mixed
    {
        return $this->valueDefinitions[$identifier] ?? null;
    }

    /**
     * Sets a value definition.
     *
     * @param string $identifier Value identifier.
     * @param mixed $value Value definition.
     */
    public function setValue(string $identifier, mixed $value): void
    {
        $this->valueDefinitions[$identifier] = $value;
    }

    /**
     * Checks if a value is defined.
     *
     * @param string $identifier Value identifier.
     *
     * @return bool If value is defined.
     */
    public function hasValue(string $identifier): bool
    {
        return array_key_exists($identifier, $this->valueDefinitions);
    }

    /**
     * Removes a value definition.
     *
     * @param string $identifier Value identifier.
     */
    public function removeValue(string $identifier): void
    {
        unset($this->valueDefinitions[$identifier]);
    }

    /**
     * Gets an instance from its class definition.
     *
     * @template T
     *
     * @param class-string<T> $className Class name.
     *
     * @return T Class instance.
     *
     * @throws ContainerException If definition is invalid or does not exist.
     */
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
                    "Container definition for {$className} is an object, but it is not an instance of the same class.",
                );
            }

            return $definition;
        }

        throw new ContainerException(
            "Container definition for {$className} is not a valid definition.",
        );
    }

    /**
     * Checks wheter a class has the `#[Lazy]` attribute.
     *
     * @param ReflectionClass $reflectionClass Class reflection.
     *
     * @return bool If class is lazy.
     */
    private function isLazy(ReflectionClass $reflectionClass): bool
    {
        return $this->hasAttribute($reflectionClass, Lazy::class);
    }

    /**
     * Checks wheter a class has the `#[Singleton]` attribute.
     *
     * @param ReflectionClass $reflectionClass Class reflection.
     *
     * @return bool If class is singleton.
     */
    private function isSingleton(ReflectionClass $reflectionClass): bool
    {
        return $this->hasAttribute($reflectionClass, Singleton::class);
    }

    /**
     * Checks wheter a class has an attribute.
     *
     * @param ReflectionClass $reflectionClass Class reflection.
     *
     * @return bool If class has the attribute.
     */
    private function hasAttribute(ReflectionClass $reflectionClass, string $attributeClass): bool
    {
        $attributes = $reflectionClass->getAttributes($attributeClass);

        return count($attributes) > 0;
    }

    /**
     * Instantiates a class with its constructor arguments.
     *
     * @param ReflectionClass $reflectionClass Class reflection.
     *
     * @return object Class instance.
     */
    private function instantiate(ReflectionClass $reflectionClass): mixed
    {
        return $reflectionClass->newInstanceArgs($this->createConstructorArgs($reflectionClass));
    }

    /**
     * Instantiates a lazy class with its constructor arguments without calling
     * the constructor yet.
     *
     * @param ReflectionClass $reflectionClass Class reflection.
     *
     * @return object Class instance.
     */
    private function instantiateLazy(ReflectionClass $reflectionClass): mixed
    {
        return $reflectionClass->newLazyGhost(
            fn($instance) => $instance->__construct(
                ...$this->createConstructorArgs($reflectionClass),
            ),
        );
    }

    /**
     * Creates the constructor arguments for a class.
     *
     * @param ReflectionClass $reflectionClass Class reflection.
     *
     * @return mixed[] Constructor arguments.
     *
     * @throws ContainerException If constructor arguments are invalid (i.e. refer to the same
     * entity, has no injection configuration or does not exist).
     */
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
                    "Container cannot inject {$paramType}. A constructor dependency cannot refer to the same class.",
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

    /**
     * Gets a parameter value from its definition on the container.
     *
     * @param ReflectionParameter $param Parameter reflection.
     *
     * @return mixed Parameter value.
     *
     * @throws ContainerException If definition is invalid or does not exist.
     */
    private function getParamValueFromDefinition(ReflectionParameter $param): mixed
    {
        $injectAttribute = $param->getAttributes(Inject::class)[0]?->newInstance();

        if ($injectAttribute === null) {
            return null;
        }

        $value = $this->getValue($injectAttribute->identifier);

        if ($value === null) {
            throw new ContainerException(
                "Container cannot inject \"{$injectAttribute->identifier}\". It is not defined.",
            );
        }

        $paramType = $param->getType()->getName();
        $valueType = gettype($value);

        if ($paramType !== $valueType) {
            throw new ContainerException(
                sprintf(
                    'Container cannot inject "%s". It is not the same type as the parameter. Expected %s, got %s.',
                    $injectAttribute->identifier,
                    $paramType,
                    $valueType,
                ),
            );
        }

        return $value;
    }
}
