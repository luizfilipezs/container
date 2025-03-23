<?php

namespace Luizfilipezs\Container\Tests\Data;

use Luizfilipezs\Container\Attributes\Inject;

class ObjectWithInjectedParam
{
    public function __construct(#[Inject('NAME')] public string $name) {}
}
