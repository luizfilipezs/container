<?php

namespace Luizfilipezs\Container\Tests\Data;

use Luizfilipezs\Container\Attributes\Inject;

class ObjectWithNullableInjectedParam
{
    public function __construct(#[Inject('KEY')] public ?string $value) {}
}
