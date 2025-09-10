<?php

declare(strict_types=1);

namespace Luizfilipezs\Container\Helpers;

final class TypeHelper
{
    /**
     * Checks whether the given type is a class or an interface.
     *
     * @param string $type Type.
     *
     * @return bool Whether type is a class or an interface.
     */
    public static function isClassOrInterface(string $type): bool
    {
        return class_exists($type) || interface_exists($type);
    }

    /**
     * Normalizes a type (i.e. converts "integer" to "int").
     *
     * @param string $type Type.
     *
     * @return string Normalized type name.
     */
    public static function normalizeName(string $type): string
    {
        return match ($type) {
            'integer' => 'int',
            'boolean' => 'bool',
            'double' => 'float',
            'NULL' => 'null',
            default => $type,
        };
    }

    public static function getNormalizedTypeOf(mixed $value): string
    {
        return self::normalizeName(gettype($value));
    }
}
