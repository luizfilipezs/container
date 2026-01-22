<?php

declare(strict_types=1);

namespace Luizfilipezs\Container\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

class ContainerNotFoundException extends ContainerException implements NotFoundExceptionInterface {}
