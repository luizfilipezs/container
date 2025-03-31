<?php

declare(strict_types=1);

namespace Luizfilipezs\Container;

use Luizfilipezs\Container\Attributes\{Inject, Lazy, LazyInitializationSkipped, Singleton};
use Luizfilipezs\Container\Enums\ContainerEvent;
use Luizfilipezs\Container\Events\{ContainerEventHandler};
use Luizfilipezs\Container\Exceptions\ContainerException;
use Luizfilipezs\Container\Interfaces\{ContainerEventHandlerInterface};
use ReflectionAttribute;
use ReflectionClass;
use ReflectionParameter;

/**
 * Dependency injection container.
 */
class Container
{
    /**
     * Container event handler.
     */
    private(set) public ContainerEventHandlerInterface $eventHandler;

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
     * Reflection classes cache.
     *
     * @var array<class-string,ReflectionClass>
     */
    private array $reflectionClasses = [];

    /**
     * Constructor.
     *
     * @param bool $strict Wether to provide only defined classes and values. If true, only
     * defined classes and values will be provided. Defaults to `false`.
     * @param bool $skipNullableClassParams If true, parameters typed as some class or null
     * will be skipped. Defaults to `true`.
     * @param bool $skipNullableValueParams If true, parameters typed as some value or null
     * will be skipped. Defaults to `true`.
     */
    public function __construct(
        public readonly bool $strict = false,
        public readonly bool $skipNullableClassParams = true,
        public readonly bool $skipNullableValueParams = true,
    ) {
        $this->eventHandler = new ContainerEventHandler();
    }

    /**
     * Gets a class instance.
     *
     * @template T
     *
     * @param class-string<T> $className Class name.
     *
     * @throws ContainerException If instance cannot be created.
     * @return T Class instance.
     *
     */
    public function get(string $className): mixed
    {
        $this->eventHandler->emit(ContainerEvent::BEFORE_RESOLVE, $className);

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
     * @param T|class-string<T>|callable|callable(): T|null $definition Class definition.
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
     * Returns all class definitions.
     *
     * @return array<string,class-string|callable|object> Class definitions.
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * Gets a class definition.
     *
     * @template T
     *
     * @param class-string<T> $className Class name.
     *
     * @return T|class-string<T>|callable(): T|null Class definition.
     */
    public function getDefinition(string $className): mixed
    {
        return $this->definitions[$className] ?? null;
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
     * Returns all value definitions.
     *
     * @return array<string,mixed> Value definitions.
     */
    public function getValueDefinitions(): mixed
    {
        return $this->valueDefinitions;
    }

    /**
     * Gets a value definition.
     *
     * @param string $identifier Value identifier.
     *
     * @return mixed Value definition.
     */
    public function getValueDefinition(string $identifier): mixed
    {
        return $this->valueDefinitions[$identifier] ?? null;
    }

    /**
     * Gets an instance from its class definition.
     *
     * @template T
     *
     * @param class-string<T> $className Class name.
     *
     * @throws ContainerException If definition is invalid or does not exist.
     * @return T Class instance.
     *
     */
    private function getFromDefinition(string $className): mixed
    {
        $definition = $this->getDefinition($className);

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
     * @throws ContainerException If class string definition is invalid.
     * @return T Class instance.
     *
     */
    private function getFromClassStringDefinition(string $className, string $definition): mixed
    {
        if (!class_exists($definition)) {
            throw new ContainerException(
                "Container definition for {$className} is a string, but it is not a valid class name.",
            );
        }

        if (
            $definition !== $className &&
            ($subDefinition = $this->definitions[$definition] ?? null) !== null &&
            $subDefinition instanceof $className
        ) {
            return $subDefinition;
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
     * @throws ContainerException If the callable does not return an instance of the expected class.
     * @return T Class instance.
     *
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
     * @throws ContainerException If the object definition is not an instance of the same class.
     * @return T Class instance.
     *
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
     * @throws \ReflectionException If the class does not exist.
     * @return T Class instance.
     *
     */
    private function getUndefined(string $className): mixed
    {
        $reflectionClass = $this->getReflectionClass($className);
        $instance = $this->isLazy($reflectionClass)
            ? $this->instantiateLazy($reflectionClass)
            : $this->instantiate($reflectionClass);

        if ($this->isSingleton($reflectionClass)) {
            $this->set($className, $instance);
        }

        return $instance;
    }

    /**
     * Gets a class reflection from cache or creates a new one.
     *
     * @param string $className Class name.
     *
     * @throws \ReflectionException If the class does not exist.
     * @return ReflectionClass Class reflection.
     *
     */
    private function getReflectionClass(string $className): ReflectionClass
    {
        if (!isset($this->reflectionClasses[$className])) {
            $this->reflectionClasses[$className] = new ReflectionClass($className);
        }

        return $this->reflectionClasses[$className];
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
     * @param string $attributeClass
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

        $instance = $reflectionClass->newLazyGhost(
            fn (object $unconstructedInstance) => $this->constructLazyGhost(
                $reflectionClass,
                $unconstructedInstance,
            ),
        );

        $this->enableLazyInitializationSkippingProperties($reflectionClass, $instance);

        return $instance;
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
     * Calls the constructor of a lazy class.
     *
     * @param ReflectionClass $reflectionClass Class reflection.
     * @param object $unconstructedInstance Instance to be constructed.
     */
    private function constructLazyGhost(
        ReflectionClass $reflectionClass,
        object $unconstructedInstance,
    ): void {
        $unconstructedInstance->__construct(...$this->createConstructorArgs($reflectionClass));

        $this->eventHandler->emit(
            ContainerEvent::LAZY_CLASS_CONSTRUCTED,
            $reflectionClass->getName(),
            $unconstructedInstance,
        );
    }

    /**
     * Configures properties that can skip lazy initialization.
     *
     * @param ReflectionClass $reflectionClass Class reflection.
     * @param object $classInstance Class instance to the which the configuration is applied.
     */
    private function enableLazyInitializationSkippingProperties(
        ReflectionClass $reflectionClass,
        object $classInstance,
    ): void {
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $skipsInitialization =
                count($reflectionProperty->getAttributes(LazyInitializationSkipped::class)) > 0;

            if ($skipsInitialization) {
                $reflectionProperty->skipLazyInitialization($classInstance);
            }
        }
    }

    /**
     * Creates the constructor arguments for a class.
     *
     * @param ReflectionClass $reflectionClass Class reflection.
     *
     * @throws ContainerException If constructor arguments are invalid (i.e. refer to the same
     * entity, has no injection configuration or does not exist).
     * @return mixed[] Constructor arguments.
     *
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
                $arguments[] = $this->getInjectableParamValue($param, $injectAttribute);
                continue;
            }

            if ($this->canSkipParam($param)) {
                $arguments[] = null;
                continue;
            }

            if (in_array($paramType, ['self', 'parent', 'static'])) {
                throw new ContainerException(
                    "Container cannot inject {$paramType}. A constructor dependency cannot refer to itself.",
                );
            }

            if ($this->isClassOrInterface($paramType)) {
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
     * @param ReflectionAttribute $injectAttribute `Inject` attribute reflection.
     *
     * @throws ContainerException If definition is invalid or does not exist.
     * @return mixed Parameter value.
     *
     */
    private function getInjectableParamValue(
        ReflectionParameter $param,
        ReflectionAttribute $injectAttribute,
    ): mixed {
        $definition = $injectAttribute->newInstance()->identifier;
        $value = $this->getValue($definition);

        if ($value === null) {
            return $this->handleParamNullInjection($param, $definition);
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
     * Identifies the proper argument to be injected into a parameter when the configured injection
     * is null.
     *
     * @param ReflectionParameter $param Parameter reflection.
     * @param string $definition Injection identifier.
     *
     * @return object|null Parameter argument
     */
    private function handleParamNullInjection(
        ReflectionParameter $param,
        string $definition,
    ): ?object {
        if ($this->isClassOrInterface($definition)) {
            return $this->get($definition);
        }

        if ($param->allowsNull()) {
            return null;
        }

        throw new ContainerException(
            "Container cannot inject \"{$definition}\". It is null and parameter is not nullable.",
        );
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
            'double' => 'float',
            'NULL' => 'null',
            default => $type,
        };
    }

    /**
     * Checks if a parameter can be set as `null`.
     *
     * @param ReflectionParameter $param Parameter reflection.
     *
     * @return bool If parameter can be skipped.
     */
    private function canSkipParam(ReflectionParameter $param): bool
    {
        if (!$param->allowsNull()) {
            return false;
        }

        if ($this->isClassOrInterface($param->getType()->getName())) {
            return $this->skipNullableClassParams;
        }

        return $this->skipNullableValueParams;
    }

    /**
     * Checks if the given type is a class or an interface.
     *
     * @param string $type Type.
     *
     * @return bool If type is a class or an interface.
     */
    private function isClassOrInterface(string $type): bool
    {
        return class_exists($type) || interface_exists($type);
    }
}
