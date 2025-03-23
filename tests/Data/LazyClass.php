<?php

namespace Luizfilipezs\Container\Tests\Data;

use Luizfilipezs\Container\Attributes\Lazy;

#[Lazy]
class LazyClass
{
    public $foo = 'bar';

    public function __construct()
    {
        throw new \Exception('Lazy constructor called.');
    }
}
