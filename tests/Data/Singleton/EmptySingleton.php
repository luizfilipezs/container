<?php

namespace Luizfilipezs\Container\Tests\Data\Singleton;

use Luizfilipezs\Container\Attributes\Singleton;
use Luizfilipezs\Container\Tests\Data\Interfaces\EmptyInterface;

#[Singleton]
class EmptySingleton implements EmptyInterface {}
