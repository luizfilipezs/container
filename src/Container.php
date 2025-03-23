<?php

namespace Luizfilipezs\Container;

use Luizfilipezs\Container\Attributes\{Inject, Lazy, Singleton};
use Luizfilipezs\Container\Enums\EventName;
use Luizfilipezs\Container\Events\EventHandler;
use Luizfilipezs\Container\Exceptions\ContainerException;
use Luizfilipezs\Container\Interfaces\EventHandlerInterface;
use ReflectionClass;
use ReflectionParameter;

/**
 * Dependency injection container.
 */
class Container
{
    /**
     * If true, only defined classes and values will be provided.
     */
    private(set) bool $strict = false;

    /**
     * Event handler.
     */
    private readonly EventHandlerInterface $eventHandler;

    /**
     * Constructor.
     * 
     * @param bool $strict If true, only defined classes and values will be provided.
     * @param bool $skipNullParams If true, null parameters will not be injected.
     */
    public function __construct(
        bool $strict = false,
        public readonly bool $skipNullParams = true,
    ) {
        $this->eventHandler = $this->get(EventHandler::class);
        $this->strict = $strict;
    }

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

        if ($this->strict) {
            throw new ContainerException("{$className} has no definition.");
        }

        if (!class_exists($className)) {
            throw new ContainerException("{$className} is not a class and cannot be instantiated.");
        }

        return $this->getUndefined($className);
    }

    /**
     * Sets a class definition.
     *
     * @template T
     *
     * @param class-string<T> $className Class name.
     * @param null|T|class-string<T>|callable|callable(): T $definition Class definition.
     * If null, the class name will be used as definition.
     */
    public function set(string $className, mixed $definition = null): void
    {
        $this->definitions[$className] = $definition ?? $className;
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
        $value = $this->valueDefinitions[$identifier] ?? null;

        if ($value === null && $this->strict) {
            throw new ContainerException("{$identifier} has no definition.");
        }

        return $value;
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
            return $this->getFromClassStringDefinition($className, $definition);
        }

        if (is_callable($definition)) {
            return $this->getFromCallableDefinition($className, $definition);
        }

        if (is_object($definition)) {
            return $this->getFromObjectDefinition($className, $definition);
        }

        throw new ContainerException(
            "Container definition for {$className} is not a valid definition.",
        );
    }

    /**
     * Gets a class instance from its class string definition.
     *
     * @template T
     *
     * @param class-string<T> $className Class name.
     * @param string $definition Class string definition.
     *
     * @return T Class instance.
     *
     * @throws ContainerException If class string definition is invalid.
     */
    private function getFromClassStringDefinition(string $className, string $definition): mixed
    {
        if (!class_exists($definition)) {
            throw new ContainerException(
                "Container definition for {$className} is a string, but it is not a valid class name.",
            );
        }

        return $this->getUndefined($definition);
    }

    /**
     * Gets a class instance from its callable definition.
     *
     * @template T
     *
     * @param class-string<T> $className Class name.
     * @param callable(): T $definition Callable that returns a class instance.
     *
     * @return T Class instance.
     *
     * @throws ContainerException If the callable does not return an instance of the expected class.
     */
    private function getFromCallableDefinition(string $className, callable $definition): mixed
    {
        $callableReturn = $definition();

        if (!is_object($callableReturn) || !$callableReturn instanceof $className) {
            throw new ContainerException(
                "Container definition for {$className} is a callable that does not return an instance of the expected class.",
            );
        }

        return $callableReturn;
    }

    /**
     * Gets a class instance from its object definition.
     *
     * @template T
     *
     * @param class-string<T> $className Class name.
     * @param object $definition Class instance definition.
     *
     * @return T Class instance.
     *
     * @throws ContainerException If the object definition is not an instance of the same class.
     */
    private function getFromObjectDefinition(string $className, object $definition): mixed
    {
        if (!$definition instanceof $className) {
            throw new ContainerException(
                "Container definition for {$className} is an object, but it is not an instance of the same class.",
            );
        }

        return $definition;
    }

    /**
     * Gets a class without definition.
     *
     * @template T
     *
     * @param class-string<T> $className Class name.
     *
     * @return T Class instance.
     */
    private function getUndefined(string $className): mixed
    {
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
        $this->checkLazyConstructor($reflectionClass);

        return $reflectionClass->newLazyGhost(function ($instance) use ($reflectionClass) {
            $instance->__construct(...$this->createConstructorArgs($reflectionClass));

            $this->eventHandler->emit(
                EventName::LAZY_CLASS_CONSTRUCTED->value,
                $reflectionClass->getName(),
                $instance,
            );
        });
    }

    /**
     * Checks if a class has a constructor. Only classes with a constructor can
     * be lazy.
     *
     * @param ReflectionClass $reflectionClass Class reflection.
     *
     * @throws ContainerException If the class has no constructor.
     */
    private function checkLazyConstructor(ReflectionClass $reflectionClass)
    {
        $reflectionConstructor = $reflectionClass->getConstructor();

        if ($reflectionConstructor === null) {
            throw new ContainerException(
                sprintf(
                    'Lazy class %s has no constructor. Only classes with a constructor can be lazy.',
                    $reflectionClass->getName(),
                ),
            );
        }
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
            $injectAttribute = $param->getAttributes(Inject::class)[0] ?? null;

            if ($injectAttribute !== null) {
                $arguments[] = $this->getParamValueFromDefinition(
                    param: $param,
                    definition: $injectAttribute->newInstance()->identifier,
                );
                continue;
            }

            if ($this->skipNullParams && $param->allowsNull()) {
                $arguments[] = null;
                continue;
            }

            if (in_array($paramType, ['self', 'parent', 'static'])) {
                throw new ContainerException(
                    "Container cannot inject {$paramType}. A constructor dependency cannot refer to the same class.",
                );
            }

            if (class_exists($paramType)) {
                $arguments[] = $this->get($paramType);
                continue;
            }

            throw new ContainerException(
                "Container cannot inject {$paramType}. It is not a valid class name and has no injection configuration.",
            );
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
    private function getParamValueFromDefinition(ReflectionParameter $param, string $definition): mixed
    {
        $value = $this->getValue($definition);

        if ($value === null) {
            if (!$param->allowsNull()) {
                throw new ContainerException(
                    "Container cannot inject \"{$definition}\". It is null and parameter is not nullable.",
                );
            }
            
            return null;
        }
        
        $paramType = $param->getType()->getName();
        $valueType = $this->normalizeType(gettype($value));
        
        if ($paramType !== $valueType) {
            throw new ContainerException(
                sprintf(
                    'Container cannot inject "%s". It is not the same type as the parameter. Expected %s, got %s.',
                    $definition,
                    $paramType,
                    $valueType,
                ),
            );
        }

        return $value;
    }

    /**
     * Normalizes a type (i.e. converts "integer" to "int").
     * 
     * @param string $type Type.
     */
    private function normalizeType(string $type): string
    {
        return match ($type) {
            'integer' => 'int',
            'boolean' => 'bool',
            'double'  => 'float',
            'NULL'    => 'null',
            default   => $type,
        };
    }
}
