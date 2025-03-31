<?php

declare(strict_types=1);

namespace Luizfilipezs\Container\Tests\Data;

class ObjectWithSelfParam
{
    public function __construct(self $self) {}
}
