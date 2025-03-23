<?php

namespace Luizfilipezs\Container\Events;

use Luizfilipezs\Container\Attributes\Singleton;
use Luizfilipezs\Container\Interfaces\EventHandlerInterface;

#[Singleton]
class EventHandler implements EventHandlerInterface
{
    private array $events = [];

    public function on(string $event, callable $callback): void
    {
        $this->events[$event][] = $callback;
    }

    public function off(string $event, callable $callback): void
    {
        if (isset($this->events[$event])) {
            unset($this->events[$event][array_search($callback, $this->events[$event])]);
        }
    }

    public function emit(string $event, ...$args): void
    {
        if (isset($this->events[$event])) {
            foreach ($this->events[$event] as $callback) {
                $callback(...$args);
            }
        }
    }
}
