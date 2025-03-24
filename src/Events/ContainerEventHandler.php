<?php

namespace Luizfilipezs\Container\Events;

use Luizfilipezs\Container\Attributes\Singleton;
use Luizfilipezs\Container\Enums\ContainerEvent;
use Luizfilipezs\Container\Interfaces\ContainerEventHandlerInterface;

#[Singleton]
final class ContainerEventHandler implements ContainerEventHandlerInterface
{
    /**
     * Event callback configurations.
     *
     * @var array<string,array{callback: callable, isOnce: bool}>
     */
    private array $callbacks = [];

    /**
     * {@inheritdoc}
     */
    public function once(ContainerEvent $event, callable $callback): void
    {
        $this->callbacks[$event->value][] = [
            'callback' => $callback,
            'isOnce' => true,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function on(ContainerEvent $event, callable $callback): void
    {
        $this->callbacks[$event->value][] = [
            'callback' => $callback,
            'isOnce' => false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function off(ContainerEvent $event, callable $callback): void
    {
        if (!isset($this->callbacks[$event->value])) {
            return;
        }

        $this->callbacks[$event->value] = array_filter(
            $this->callbacks[$event->value],
            fn($item) => $item['callback'] !== $callback,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function emit(ContainerEvent $event, mixed ...$args): void
    {
        if (!isset($this->callbacks[$event->value])) {
            return;
        }

        $callbacksToExecute = $this->callbacks[$event->value];

        foreach ($callbacksToExecute as $item) {
            $item['callback'](...$args);

            if ($item['isOnce']) {
                $this->off($event, $item['callback']);
            }
        }
    }
}
