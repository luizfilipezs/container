<?php

namespace Luizfilipezs\Container\Tests\Data;

class ObjectWithSelfParam
{
    public function __construct(self $self) {}
}
