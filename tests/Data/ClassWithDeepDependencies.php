<?php

namespace Luizfilipezs\Container\Tests\Data;

class ClassWithDeepDependencies
{
    public function __construct(
        public ClassWithDependencies $obj1,
        public ClassWithDependencies $obj2,
    ) {}
}
