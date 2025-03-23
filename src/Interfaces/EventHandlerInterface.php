<?php

namespace Luizfilipezs\Container\Interfaces;

interface EventHandlerInterface
{
    public function on(string $event, callable $callback): void;

    public function off(string $event, callable $callback): void;

    public function emit(string $event, ...$args): void;
}
