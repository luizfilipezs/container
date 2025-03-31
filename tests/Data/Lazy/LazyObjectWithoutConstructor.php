<?php

declare(strict_types=1);

namespace Luizfilipezs\Container\Tests\Data\Lazy;

use Luizfilipezs\Container\Attributes\Lazy;

#[Lazy]
class LazyObjectWithoutConstructor
{
    public string $foo = 'bar';
}
