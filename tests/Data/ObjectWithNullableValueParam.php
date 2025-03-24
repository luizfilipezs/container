<?php

namespace Luizfilipezs\Container\Tests\Data;

class ObjectWithNullableValueParam
{
    public function __construct(public readonly ?string $value) {}
}
