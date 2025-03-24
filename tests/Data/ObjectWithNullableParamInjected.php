<?php

namespace Luizfilipezs\Container\Tests\Data;

use Luizfilipezs\Container\Attributes\Inject;

class ObjectWithNullableParamInjected
{
    public function __construct(
        #[
            Inject(ObjectWithoutConstructor::class),
        ]
        public readonly ?ObjectWithoutConstructor $nullableDep = null,
    ) {}
}
