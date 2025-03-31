<?php

declare(strict_types=1);

namespace Luizfilipezs\Container\Tests\Data\Lazy;

use Luizfilipezs\Container\Attributes\{Lazy, LazyInitializationSkipped};

#[Lazy]
class LazyObjectWithSkippedAttribute
{
    #[LazyInitializationSkipped]
    public string $skippedProp = 'foo';

    public string $normalProp = 'bar';

    public function __construct() {}
}
