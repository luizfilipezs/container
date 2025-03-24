<?php

namespace Luizfilipezs\Container\Tests\Unit\Events;

use Luizfilipezs\Container\Enums\ContainerEvent;
use Luizfilipezs\Container\Events\ContainerEventHandler;
use PHPUnit\Framework\TestCase;

final class ContainerEventHandlerTest extends TestCase
{
    /**
     * Event handler.
     */
    private ?ContainerEventHandler $containerEventHandler;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->containerEventHandler = new ContainerEventHandler();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(): void
    {
        $this->containerEventHandler = null;
    }

    public function testOnce(): void
    {
        $called = false;

        $this->containerEventHandler->once(
            event: ContainerEvent::LAZY_CLASS_CONSTRUCTED,
            callback: static function () use (&$called) {
                $called = true;
            },
        );

        $this->containerEventHandler->emit(ContainerEvent::LAZY_CLASS_CONSTRUCTED);
        $this->assertTrue($called, 'Callback once should be called on first emit');

        $called = false;
        $this->containerEventHandler->emit(ContainerEvent::LAZY_CLASS_CONSTRUCTED);
        $this->assertFalse($called, 'Callback once should not be called on second emit');
    }

    public function testOn(): void
    {
        $callTimes = 0;

        $this->containerEventHandler->on(
            event: ContainerEvent::LAZY_CLASS_CONSTRUCTED,
            callback: static function () use (&$callTimes) {
                $callTimes++;
            },
        );

        $this->containerEventHandler->emit(ContainerEvent::LAZY_CLASS_CONSTRUCTED);
        $this->containerEventHandler->emit(ContainerEvent::LAZY_CLASS_CONSTRUCTED);

        $this->assertEquals(2, $callTimes, 'Callback on should be called on every emit');
    }

    public function testOff(): void
    {
        $called = false;
        $callback = static function () use (&$called) {
            $called = true;
        };

        $this->containerEventHandler->on(ContainerEvent::LAZY_CLASS_CONSTRUCTED, $callback);
        $this->containerEventHandler->off(ContainerEvent::LAZY_CLASS_CONSTRUCTED, $callback);

        $this->containerEventHandler->emit(ContainerEvent::LAZY_CLASS_CONSTRUCTED);
        $this->assertFalse($called, 'Callback should not be called after being removed with off');
    }

    public function testOffWithOnceCallback(): void
    {
        $called = false;
        $callback = static function () use (&$called) {
            $called = true;
        };

        $this->containerEventHandler->once(ContainerEvent::LAZY_CLASS_CONSTRUCTED, $callback);
        $this->containerEventHandler->off(ContainerEvent::LAZY_CLASS_CONSTRUCTED, $callback);

        $this->containerEventHandler->emit(ContainerEvent::LAZY_CLASS_CONSTRUCTED);
        $this->assertFalse(
            $called,
            'Once callback should not be called after being removed with off',
        );
    }

    public function testMultipleCallbacks(): void
    {
        $callCount1 = 0;
        $callCount2 = 0;

        $callback1 = static function () use (&$callCount1) {
            $callCount1++;
        };
        $callback2 = static function () use (&$callCount2) {
            $callCount2++;
        };

        $this->containerEventHandler->on(ContainerEvent::LAZY_CLASS_CONSTRUCTED, $callback1);
        $this->containerEventHandler->on(ContainerEvent::LAZY_CLASS_CONSTRUCTED, $callback2);

        $this->containerEventHandler->emit(ContainerEvent::LAZY_CLASS_CONSTRUCTED);

        $this->assertEquals(1, $callCount1, 'First callback should be called once');
        $this->assertEquals(1, $callCount2, 'Second callback should be called once');
    }

    public function testEmitWithNoCallbacks(): void
    {
        $this->expectNotToPerformAssertions();
        $this->containerEventHandler->emit(ContainerEvent::LAZY_CLASS_CONSTRUCTED);
    }

    public function testOffWithNonExistentCallback(): void
    {
        $nonExistentCallback = static function () {};
        $this->containerEventHandler->off(
            ContainerEvent::LAZY_CLASS_CONSTRUCTED,
            $nonExistentCallback,
        );
        $this->assertTrue(true, 'Removing non-existent callback should not cause errors');
    }

    public function testEmitWithArguments(): void
    {
        $receivedArgs = [];
        $callback = static function (...$args) use (&$receivedArgs) {
            $receivedArgs = $args;
        };

        $this->containerEventHandler->on(ContainerEvent::LAZY_CLASS_CONSTRUCTED, $callback);
        $this->containerEventHandler->emit(ContainerEvent::LAZY_CLASS_CONSTRUCTED, 'arg1', 123, [
            'key' => 'value',
        ]);

        $this->assertEquals(
            ['arg1', 123, ['key' => 'value']],
            $receivedArgs,
            'Callback should receive correct arguments',
        );
    }

    public function testDifferentEvents(): void
    {
        $calledEvent1 = false;
        $calledEvent2 = false;

        $callback1 = static function () use (&$calledEvent1) {
            $calledEvent1 = true;
        };
        $callback2 = static function () use (&$calledEvent2) {
            $calledEvent2 = true;
        };

        $this->containerEventHandler->on(ContainerEvent::LAZY_CLASS_CONSTRUCTED, $callback1);
        $this->containerEventHandler->on(ContainerEvent::BEFORE_RESOLVE, $callback2);

        $this->containerEventHandler->emit(ContainerEvent::LAZY_CLASS_CONSTRUCTED);

        $this->assertTrue($calledEvent1, 'Callback for first event should be called');
        $this->assertFalse($calledEvent2, 'Callback for second event should not be called');
    }

    public function testOnceCallbacksAreClearedAfterEmit(): void
    {
        $callCount = 0;
        $callback = static function () use (&$callCount) {
            $callCount++;
        };

        $this->containerEventHandler->once(ContainerEvent::LAZY_CLASS_CONSTRUCTED, $callback);
        $this->containerEventHandler->emit(ContainerEvent::LAZY_CLASS_CONSTRUCTED);
        $this->containerEventHandler->emit(ContainerEvent::LAZY_CLASS_CONSTRUCTED);

        $this->assertEquals(1, $callCount, 'Once callback should only be called once');
    }

    public function testCallbackOrder(): void
    {
        $executionOrder = [];

        $callback1 = static function () use (&$executionOrder) {
            $executionOrder[] = 1;
        };
        $callback2 = static function () use (&$executionOrder) {
            $executionOrder[] = 2;
        };
        $callback3 = static function () use (&$executionOrder) {
            $executionOrder[] = 3;
        };

        $this->containerEventHandler->on(ContainerEvent::LAZY_CLASS_CONSTRUCTED, $callback1);
        $this->containerEventHandler->once(ContainerEvent::LAZY_CLASS_CONSTRUCTED, $callback2);
        $this->containerEventHandler->on(ContainerEvent::LAZY_CLASS_CONSTRUCTED, $callback3);

        $this->containerEventHandler->emit(ContainerEvent::LAZY_CLASS_CONSTRUCTED);

        $this->assertEquals(
            [1, 2, 3],
            $executionOrder,
            'Callbacks should execute in registration order',
        );
    }
}
