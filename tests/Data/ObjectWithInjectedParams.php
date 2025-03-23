<?php

namespace Luizfilipezs\Container\Tests\Data;

use Luizfilipezs\Container\Attributes\Inject;

class ObjectWithInjectedParams
{
    public function __construct(
        #[Inject('NAME')] public string $name,
        #[Inject('AGE')] public int $age,
    ) {}
}
