<?php

namespace Luizfilipezs\Container\Enums;

/**
 * Events emitted by the container.
 */
enum ContainerEvent: string
{
    /**
     * Event emitted before a class is resolved.
     */
    case BEFORE_RESOLVE = 'beforeResolve';

    /**
     * Event emitted when a lazy class is constructed.
     */
    case LAZY_CLASS_CONSTRUCTED = 'lazyClassConstructed';
}
