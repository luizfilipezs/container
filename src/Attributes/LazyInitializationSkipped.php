<?php

namespace Luizfilipezs\Container\Attributes;

/**
 * Avoids lazy initialization for reading the bound property.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class LazyInitializationSkipped {}
