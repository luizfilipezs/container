<?php

namespace Luizfilipezs\Container\Tests\Data;

class ObjectWithDependencies
{
    public function __construct(
        public readonly ObjectWithoutConstructor $dep1,
        public readonly ObjectWithoutConstructor $dep2,
    ) {}
}
