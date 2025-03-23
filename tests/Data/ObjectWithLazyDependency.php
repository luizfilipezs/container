<?php

namespace Luizfilipezs\Container\Tests\Data;

use Luizfilipezs\Container\Tests\Data\Lazy\LazyObject;

class ObjectWithLazyDependency
{
    public function __construct(public readonly LazyObject $lazyDep) {}
}
