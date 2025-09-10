<?php

declare(strict_types=1);

namespace Luizfilipezs\Container\Tests\Unit;

use Luizfilipezs\Container\Container;
use Luizfilipezs\Container\Tests\Data\Interfaces\EmptyInterface;
use Luizfilipezs\Container\Tests\Data\Singleton\EmptySingleton;
use PHPUnit\Framework\TestCase;

final class SingletonTest extends TestCase
{
    public function testSingletonDefinedViaInterface(): void
    {
        $container = new Container();
        $container->set(EmptyInterface::class, EmptySingleton::class);

        $instance1 = $container->get(EmptyInterface::class);
        $this->assertInstanceOf(EmptySingleton::class, $instance1);

        $instance2 = $container->get(EmptyInterface::class);
        $this->assertSame($instance1, $instance2);
    }
}
