<?php

namespace Luizfilipezs\Container\Interfaces;

use Luizfilipezs\Container\Enums\ContainerEvent;

/**
 * Implements methods for handling container events.
 */
interface ContainerEventHandlerInterface
{
    /**
     * Sets a callback to be executed once.
     *
     * @param ContainerEvent $event Container event.
     * @param callable $callback Callback to be executed.
     */
    public function once(ContainerEvent $event, callable $callback): void;

    /**
     * Sets a callback to be executed every time the event is triggered.
     *
     * @param ContainerEvent $event Container event.
     * @param callable $callback Callback to be executed.
     */
    public function on(ContainerEvent $event, callable $callback): void;

    /**
     * Removes a callback from the event.
     *
     * @param ContainerEvent $event Container event.
     * @param callable $callback Callback to be removed.
     */
    public function off(ContainerEvent $event, callable $callback): void;

    /**
     * Triggers the event.
     *
     * @param ContainerEvent $event Container event.
     * @param mixed ...$args Event arguments.
     */
    public function emit(ContainerEvent $event, ...$args): void;
}
