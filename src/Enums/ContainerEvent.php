<?php

namespace Luizfilipezs\Container\Enums;

enum ContainerEvent: string
{
    case LAZY_CLASS_CONSTRUCTED = 'lazyClassConstructed';
}
