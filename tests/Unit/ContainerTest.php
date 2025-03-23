<?php

namespace Luizfilipezs\Container\Tests\Unit;

use Luizfilipezs\Container\Container;
use Luizfilipezs\Container\Exceptions\ContainerException;
use Luizfilipezs\Container\Tests\Data\ObjectWithoutConstructor;
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
        $container = new Container(strict: true);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(ObjectWithoutConstructor::class . ' has no definition.');

        $container->get(ObjectWithoutConstructor::class);
    }
}
