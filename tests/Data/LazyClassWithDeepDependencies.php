<?php

namespace Luizfilipezs\Container\Tests\Data;

use Luizfilipezs\Container\Attributes\LazyGhost;

#[LazyGhost]
class LazyClassWithDeepDependencies
{
    public function __construct(
        private ClassWithoutDependencies $dep1,
        private ClassWithDependencies $dep2,
        private ClassWithDeepDependencies $dep3,
        private LazyClass $lazyDep,
    ) {
        throw new \Exception('Lazy constructor called.');
    }

    public function getLazyDependency(): LazyClass
    {
        return $this->lazyDep;
    }
}
