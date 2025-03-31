<?php

declare(strict_types=1);

namespace Luizfilipezs\Container\Tests\Data\Lazy;

use Luizfilipezs\Container\Attributes\Lazy;

#[Lazy]
class LazyObject
{
    public string $foo = 'bar';

    public function __construct() {}
}
