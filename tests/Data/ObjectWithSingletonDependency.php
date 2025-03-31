<?php

declare(strict_types=1);

namespace Luizfilipezs\Container\Tests\Data;

use Luizfilipezs\Container\Tests\Data\Singleton\SingletonObject;

class ObjectWithSingletonDependency
{
    public function __construct(public readonly SingletonObject $singletonDep) {}
}
