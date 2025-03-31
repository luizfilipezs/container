<?php

declare(strict_types=1);

namespace Luizfilipezs\Container\Tests\Data;

class ObjectWithParentParam extends ObjectWithoutConstructor
{
    public function __construct(parent $parent) {}
}
