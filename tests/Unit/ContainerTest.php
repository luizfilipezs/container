<?php

namespace Luizfilipezs\Container\Tests\Unit;

use Luizfilipezs\Container\Container;
use Luizfilipezs\Container\Enums\EventName;
use Luizfilipezs\Container\Events\EventHandler;
use Luizfilipezs\Container\Exceptions\ContainerException;
use Luizfilipezs\Container\Interfaces\EventHandlerInterface;
use Luizfilipezs\Container\Tests\Data\Lazy\{LazyObject, LazyObjectWithoutConstructor};
use Luizfilipezs\Container\Tests\Data\Singleton\SingletonObject;
use Luizfilipezs\Container\Tests\Data\{
    ObjectWithDeepDependencies,
    ObjectWithDependencies,
    ObjectWithInjectedParams,
    ObjectWithLazyDependency,
    ObjectWithNullableInjectedParam,
    ObjectWithParentParam,
    ObjectWithSelfParam,
    ObjectWithSingletonDependency,
    ObjectWithoutConstructor,
};
use PHPUnit\Framework\TestCase;
use stdClass;

final class ContainerTest extends TestCase
{
    /**
     * Dependency injection container.
     */
    private ?Container $container;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        error_reporting(E_ALL); // show warnings

        $this->container = new Container();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(): void
    {
        parent::tearDown();

        $this->container = null;
    }

    public function testHas(): void
    {
        $this->assertFalse($this->container->has(ObjectWithoutConstructor::class));
        $this->container->set(ObjectWithoutConstructor::class);
        $this->assertTrue($this->container->has(ObjectWithoutConstructor::class));
    }

    public function testRemove(): void
    {
        $this->container->set(ObjectWithoutConstructor::class);
        $this->assertTrue($this->container->has(ObjectWithoutConstructor::class));
        $this->container->remove(ObjectWithoutConstructor::class);
        $this->assertFalse($this->container->has(ObjectWithoutConstructor::class));
    }

    public function testGetClassStringDefinition(): void
    {
        $this->container->set(ObjectWithoutConstructor::class);

        $this->assertInstanceOf(
            ObjectWithoutConstructor::class,
            $this->container->get(ObjectWithoutConstructor::class),
        );
    }

    public function testGetInvalidClassStringDefinition(): void
    {
        $this->container->set(ObjectWithoutConstructor::class, 'invalid');

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Container definition for %s is a string, but it is not a valid class name.',
                ObjectWithoutConstructor::class,
            ),
        );

        $this->container->get(ObjectWithoutConstructor::class);
    }

    public function testGetCallableDefinition(): void
    {
        $instance = new ObjectWithoutConstructor();
        $this->container->set(ObjectWithoutConstructor::class, fn() => $instance);

        $this->assertSame($instance, $this->container->get(ObjectWithoutConstructor::class));
    }

    public function testGetInvalidCallableDefinition(): void
    {
        $instance = new stdClass();
        $this->container->set(ObjectWithoutConstructor::class, fn() => $instance);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Container definition for %s is a callable that does not return an instance of the expected class.',
                ObjectWithoutConstructor::class,
            ),
        );

        $this->container->get(ObjectWithoutConstructor::class);
    }

    public function testGetObjectDefinition(): void
    {
        $instance = new ObjectWithoutConstructor();
        $this->container->set(ObjectWithoutConstructor::class, $instance);

        $this->assertSame($instance, $this->container->get(ObjectWithoutConstructor::class));
    }

    public function testGetInvalidObjectDefinition(): void
    {
        $instance = new stdClass();
        $this->container->set(ObjectWithoutConstructor::class, $instance);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Container definition for %s is an object, but it is not an instance of the same class.',
                ObjectWithoutConstructor::class,
            ),
        );

        $this->container->get(ObjectWithoutConstructor::class);
    }

    public function testGetUnsetDefinition(): void
    {
        $instance = $this->container->get(ObjectWithoutConstructor::class);

        $this->assertInstanceOf(ObjectWithoutConstructor::class, $instance);
    }

    public function testGetUnsetDefinitionWithStrictOption(): void
    {
        // create container with strict option
        $container = new Container(strict: true);

        // set container dependency
        $container->set(EventHandlerInterface::class, EventHandler::class);

        // assert exception when getting undefined class
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(ObjectWithoutConstructor::class . ' has no definition.');

        // execute
        $container->get(ObjectWithoutConstructor::class);
    }

    public function testGetUnsetDefinitionWithInvalidClassName(): void
    {
        $invalidClassName = 'non-existent-class';

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            $invalidClassName . ' is not a class and cannot be instantiated.',
        );

        $this->container->get($invalidClassName);
    }

    public function testGetSingleton(): void
    {
        $obj1 = $this->container->get(SingletonObject::class);
        $obj2 = $this->container->get(SingletonObject::class);

        $this->assertSame($obj1, $obj2);
    }

    public function testGetLazyObject(): void
    {
        $constructed = false;

        $eventHandler = $this->container->get(EventHandler::class);
        $eventHandler->on(
            event: EventName::LAZY_CLASS_CONSTRUCTED->value,
            callback: function () use (&$constructed) {
                $constructed = true;
            },
        );

        $instance = $this->container->get(LazyObject::class);

        $this->assertInstanceOf(LazyObject::class, $instance);
        $this->assertFalse($constructed);

        $instance->foo;

        $this->assertTrue($constructed);
    }

    public function testGetLazyObjectWithoutConstructor(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Lazy class %s has no constructor. Only classes with a constructor can be lazy.',
                LazyObjectWithoutConstructor::class,
            ),
        );

        $this->container->get(LazyObjectWithoutConstructor::class);
    }

    public function testGetObjectWithDependencies(): void
    {
        $instance = $this->container->get(ObjectWithDependencies::class);

        $this->assertInstanceOf(ObjectWithDependencies::class, $instance);
        $this->assertInstanceOf(ObjectWithoutConstructor::class, $instance->dep1);
        $this->assertInstanceOf(ObjectWithoutConstructor::class, $instance->dep2);
    }

    public function testGetObjectWithDeepDependencies(): void
    {
        $instance = $this->container->get(ObjectWithDeepDependencies::class);

        $this->assertInstanceOf(ObjectWithDeepDependencies::class, $instance);
        $this->assertInstanceOf(ObjectWithDependencies::class, $instance->dep1);
        $this->assertInstanceOf(ObjectWithDependencies::class, $instance->dep2);
    }

    public function testGetObjectWithLazyDependency(): void
    {
        $instance = $this->container->get(ObjectWithLazyDependency::class);
        $depConstructed = false;

        $eventHandler = $this->container->get(EventHandler::class);
        $eventHandler->on(
            event: EventName::LAZY_CLASS_CONSTRUCTED->value,
            callback: function () use (&$depConstructed) {
                $depConstructed = true;
            },
        );

        $this->assertInstanceOf(ObjectWithLazyDependency::class, $instance);
        $this->assertInstanceOf(LazyObject::class, $instance->lazyDep);
        $this->assertFalse($depConstructed);

        $instance->lazyDep->foo;

        $this->assertTrue($depConstructed);
    }

    public function testGetObjectWithSingletonDependency(): void
    {
        $instance = $this->container->get(ObjectWithSingletonDependency::class);

        $this->assertInstanceOf(ObjectWithSingletonDependency::class, $instance);
        $this->assertInstanceOf(SingletonObject::class, $instance->singletonDep);

        $singletonInstance = $this->container->get(SingletonObject::class);
        $this->assertSame($singletonInstance, $instance->singletonDep);
    }

    public function testHasValue(): void
    {
        $this->assertFalse($this->container->hasValue('KEY'));
        $this->container->setValue('KEY', 'VALUE');
        $this->assertTrue($this->container->hasValue('KEY'));
    }

    public function testGetValue(): void
    {
        $this->container->setValue('KEY', 'VALUE');
        $this->assertSame('VALUE', $this->container->getValue('KEY'));
    }

    public function testGetUnsetValueWithStrictOption(): void
    {
        $container = new Container(strict: true);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('KEY has no definition.');

        $container->getValue('KEY');
    }

    public function testGetNullValueWithStrictOption(): void
    {
        $container = new Container(strict: true);
        $container->setValue('KEY', null);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('KEY has no definition.');

        $container->getValue('KEY');
    }

    public function testRemoveValue(): void
    {
        $this->container->setValue('KEY', 'VALUE');
        $this->assertSame('VALUE', $this->container->getValue('KEY'));
        $this->container->removeValue('KEY');
        $this->assertFalse($this->container->hasValue('KEY'));
    }

    public function testGetObjectWithInjectedParam(): void
    {
        $this->container->setValue('NAME', 'John');
        $this->container->setValue('AGE', 30);

        $instance = $this->container->get(ObjectWithInjectedParams::class);

        $this->assertEquals('John', $instance->name);
        $this->assertEquals(30, $instance->age);
    }

    public function testGetObjectWithInjectedParamAndInvalidType(): void
    {
        $this->container->setValue('NAME', 123);
        $this->container->setValue('AGE', 30);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            'Container cannot inject "NAME". It is not the same type as the parameter. Expected string, got int.',
        );

        $this->container->get(ObjectWithInjectedParams::class);
    }

    public function testGetObjectWithInjectedParamAndNoDefinition(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            'Container cannot inject "NAME". It is null and parameter is not nullable.',
        );

        $this->container->get(ObjectWithInjectedParams::class);
    }

    public function testGetObjectWithNullableInjectedParam(): void
    {
        $this->container->setValue('KEY', null);

        $instance = $this->container->get(ObjectWithNullableInjectedParam::class);

        $this->assertEquals(null, $instance->value);
    }

    public function testGetObjectWithSelfParam(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            'Container cannot inject self. A constructor dependency cannot refer to itself.',
        );

        $this->container->get(ObjectWithSelfParam::class);
    }

    public function testGetObjectWithParentParam(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            'Container cannot inject parent. A constructor dependency cannot refer to itself.',
        );

        $this->container->get(ObjectWithParentParam::class);
    }
}
