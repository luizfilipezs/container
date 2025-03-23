<?php

namespace Luizfilipezs\Container\Tests\Data\Lazy;

use Luizfilipezs\Container\Attributes\Lazy;

#[Lazy]
class LazyObjectWithouConstructor
{
    public string $foo = 'bar';
}
