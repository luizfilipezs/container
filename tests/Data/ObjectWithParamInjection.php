<?php

namespace Luizfilipezs\Container\Tests\Data;

use Luizfilipezs\Container\Attributes\Inject;

class ObjectWithParamInjection
{
    public function __construct(
        #[Inject('Param1')] public readonly string $a,
        #[Inject('Param2')] public readonly string $b,
    ) {}
}
