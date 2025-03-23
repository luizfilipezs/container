<?php

namespace Luizfilipezs\Container\Tests\Data;

class ObjectWithParentParam extends ObjectWithoutConstructor
{
    public function __construct(parent $parent) {}
}
