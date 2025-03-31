<?php

declare(strict_types=1);

namespace Luizfilipezs\Container\Tests\Data;

class ObjectWithNullableClassParam
{
    public function __construct(public readonly ?ObjectWithoutConstructor $nullableDep = null) {}
}
