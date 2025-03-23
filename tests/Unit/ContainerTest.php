<?php

namespace Luizfilipezs\Container\Tests\Unit;

use Luizfilipezs\Container\Container;
use Luizfilipezs\Container\Exceptions\ContainerException;
use Luizfilipezs\Container\Tests\Data\{
    ClassWithDeepDependencies,
    ClassWithDependencies,
    ClassWithoutDependencies,
    LazyClass,
    LazyClassWithDeepDependencies,
    ObjectWithParamInjection,
};
use PHPUnit\Framework\TestCase;

final class ContainerTest extends TestCase
{
    private Container $container;

    public function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
    }

    public function testGetClassWithNoDependencies(): void
    {
        $instance = $this->container->get(ClassWithoutDependencies::class);

        $this->assertInstanceOf(ClassWithoutDependencies::class, $instance);
    }

    public function testGetClassWithoutDeepDependencies(): void
    {
        $instance = $this->container->get(ClassWithDependencies::class);

        $this->assertInstanceOf(ClassWithDependencies::class, $instance);
    }

    public function testGetClassWithDeepDependencies(): void
    {
        $instance = $this->container->get(ClassWithDeepDependencies::class);

        $this->assertInstanceOf(ClassWithDeepDependencies::class, $instance);
    }

    public function testGetLazyClass(): void
    {
        // Should not throw an exception because __contruct is not called yet
        $lazyInstance = $this->container->get(LazyClass::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Lazy constructor called.');

        // Should throw an exception because __contruct is finally called
        $lazyInstance->foo;
    }

    public function testGetLazyClassWithDeepDependencies(): void
    {
        $lazyInstance = $this->container->get(LazyClassWithDeepDependencies::class);

        try {
            $lazyInstance->getLazyDependency();
            $this->fail('Instance constructed with no expected exception.');
        } catch (\Exception $e) {
            $this->assertSame('Lazy constructor called.', $e->getMessage());
        }

        try {
            $lazyInstance->getLazyDependency()->foo;
            $this->fail('Instance\'s lazy dependency constructed with no expected exception.');
        } catch (\Exception $e) {
            $this->assertSame('Lazy constructor called.', $e->getMessage());
        }
    }

    public function testParameterInjection(): void
    {
        $this->container->setValue('Param1', 'abc');
        $this->container->setValue('Param2', '123');

        $instance = $this->container->get(ObjectWithParamInjection::class);

        $this->assertSame('abc', $instance->a);
        $this->assertSame('123', $instance->b);
    }

    public function testParameterInjectionWhenValueTypeIsInvalid(): void
    {
        $this->container->setValue('Param1', 'abc'); // valid
        $this->container->setValue('Param2', ['123']); // invalid; parameter type is string

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            'Container cannot inject Param2. It is not the same type as the parameter.',
        );

        $this->container->get(ObjectWithParamInjection::class);
    }
}
