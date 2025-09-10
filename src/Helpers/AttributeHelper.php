<?php

declare(strict_types=1);

namespace Luizfilipezs\Container\Helpers;

use Luizfilipezs\Container\Attributes\{Inject, Lazy, LazyInitializationSkipped, Singleton};
use ReflectionAttribute;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;

final class AttributeHelper
{
    public static function find(
        string $attributeClass,
        ReflectionClass|ReflectionProperty|ReflectionParameter $reflection,
    ): AttributeSearchResult {
        $attributes = $reflection->getAttributes($attributeClass);

        return new AttributeSearchResult($attributeClass, $attributes);
    }

    public static function exists(
        string $attributeClass,
        ReflectionClass|ReflectionProperty|ReflectionParameter $reflection,
    ): bool {
        $searchResult = self::find($attributeClass, $reflection);

        return $searchResult->exists;
    }

    public static function hasLazy(
        ReflectionClass|ReflectionProperty|ReflectionParameter $reflection,
    ): bool {
        return self::exists(Lazy::class, $reflection);
    }

    public static function hasSingleton(
        ReflectionClass|ReflectionProperty|ReflectionParameter $reflection,
    ): bool {
        return self::exists(Singleton::class, $reflection);
    }

    public static function hasLazyInitializationSkipped(
        ReflectionClass|ReflectionProperty|ReflectionParameter $reflection,
    ): bool {
        return self::exists(LazyInitializationSkipped::class, $reflection);
    }

    public static function getInject(ReflectionParameter $reflection): ?ReflectionAttribute
    {
        return self::find(Inject::class, $reflection)->first;
    }
}
