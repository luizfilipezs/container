<?php

declare(strict_types=1);

namespace Luizfilipezs\Container\Helpers;

use ReflectionAttribute;

class AttributeSearchResult
{
    public ?ReflectionAttribute $first {
        get => $this->attributes[0] ?? null;
    }

    public bool $exists {
        get => count($this->attributes) > 0;
    }

    public function __construct(
        public readonly string $className,
        public readonly array $attributes,
    ) {}
}
