<?php

namespace Luizfilipezs\Container\Tests\Data;

class ClassWithDependencies
{
    public function __construct(
        public ClassWithoutDependencies $obj1,
        public ClassWithoutDependencies $obj2,
    ) {}
}
