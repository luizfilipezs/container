<?php

namespace Luizfilipezs\Container\Attributes;

/**
 * Binds a parameter to a symbolic name representing a value to be injected.
 *
 * Note that the injection will be prioritized over the default argument.
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
class Inject
{
    /**
     * Construtor.
     *
     * @param string $identifier Injection identifier (key set via `Container::setValue` or
     * any class name).
     */
    public function __construct(public string $identifier) {}
}
