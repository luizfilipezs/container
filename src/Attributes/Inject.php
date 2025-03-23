<?php

namespace Luizfilipezs\Container\Attributes;

/**
 * Binds a parameter to a symbolic name representing a value to be injected.
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
class Inject
{
    public function __construct(public string $identifier) {}
}
