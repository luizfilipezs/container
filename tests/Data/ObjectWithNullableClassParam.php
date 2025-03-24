<?php

namespace Luizfilipezs\Container\Tests\Data;

class ObjectWithNullableClassParam
{
    public function __construct(public readonly ?ObjectWithoutConstructor $nullableDep = null) {}
}
