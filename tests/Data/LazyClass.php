<?php

namespace Luizfilipezs\Container\Tests\Data;

use Luizfilipezs\Container\Attributes\LazyGhost;

#[LazyGhost]
class LazyClass
{
    public $foo = 'bar';

    public function __construct()
    {
        throw new \Exception('Lazy constructor called.');
    }
}
