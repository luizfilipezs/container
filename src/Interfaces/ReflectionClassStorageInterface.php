<?php

declare(strict_types=1);

namespace Luizfilipezs\Container\Interfaces;

interface ReflectionClassStorageInterface
{
    public function add(string $className): \ReflectionClass;

    public function get(string $className): ?\ReflectionClass;

    public function getOrAdd(string $className): \ReflectionClass;

    public function has(string $className): bool;
}
