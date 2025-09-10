<?php

declare(strict_types=1);

namespace Luizfilipezs\Container\Tests\Data\Singleton;

use Luizfilipezs\Container\Attributes\Singleton;

#[Singleton]
class SingletonObject
{
    public ?string $prop1 = null;
}
