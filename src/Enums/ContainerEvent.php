<?php

namespace Luizfilipezs\Container\Enums;

/**
 * Events emitted by the container.
 */
enum ContainerEvent: string
{
    /**
     * Event emitted when a lazy class is constructed.
     */
    case LAZY_CLASS_CONSTRUCTED = 'lazyClassConstructed';
}
