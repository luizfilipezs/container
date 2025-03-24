<?php

namespace Luizfilipezs\Container\Events;

use Luizfilipezs\Container\Attributes\Singleton;
use Luizfilipezs\Container\Enums\ContainerEvent;
use Luizfilipezs\Container\Interfaces\ContainerEventHandlerInterface;

#[Singleton]
class ContainerEventHandler implements ContainerEventHandlerInterface
{
    private array $events = [];
    private array $onceEvents = [];

    public function once(ContainerEvent $event, callable $callback): void
    {
        $this->events[$event->value][] = $callback;
    }

    public function on(ContainerEvent $event, callable $callback): void
    {
        $this->events[$event->value][] = $callback;
    }

    public function off(ContainerEvent $event, callable $callback): void
    {
        if (isset($this->events[$event->value])) {
            unset(
                $this->events[$event->value][array_search($callback, $this->events[$event->value])],
            );
        }

        if (isset($this->onceEvents[$event->value])) {
            unset(
                $this->onceEvents[$event->value][
                    array_search($callback, $this->onceEvents[$event->value])
                ],
            );
        }
    }

    public function emit(ContainerEvent $event, ...$args): void
    {
        $fixedCallbacks = $this->events[$event->value] ?? [];

        foreach ($fixedCallbacks as $callback) {
            $callback(...$args);
        }

        $onceCallbacks = $this->onceEvents[$event->value] ?? [];

        foreach ($onceCallbacks as $callback) {
            $callback(...$args);
            $this->off($event, $callback);
        }
    }
}
