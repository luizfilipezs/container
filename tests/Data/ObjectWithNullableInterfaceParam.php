<?php

declare(strict_types=1);

namespace Luizfilipezs\Container\Tests\Data;

use Luizfilipezs\Container\Tests\Data\Interfaces\EmptyInterface;

class ObjectWithNullableInterfaceParam
{
    public function __construct(public readonly ?EmptyInterface $nullableDep) {}
}
