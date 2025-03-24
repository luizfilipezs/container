<?php

namespace Luizfilipezs\Container\Interfaces;

use Luizfilipezs\Container\Enums\ContainerEvent;

interface ContainerEventHandlerInterface
{
    public function once(ContainerEvent $event, callable $callback): void;

    public function on(ContainerEvent $event, callable $callback): void;

    public function off(ContainerEvent $event, callable $callback): void;

    public function emit(ContainerEvent $event, ...$args): void;
}
