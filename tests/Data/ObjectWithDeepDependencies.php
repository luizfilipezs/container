<?php

declare(strict_types=1);

namespace Luizfilipezs\Container\Tests\Data;

class ObjectWithDeepDependencies
{
    public function __construct(
        public readonly ObjectWithDependencies $dep1,
        public readonly ObjectWithDependencies $dep2,
    ) {}
}
