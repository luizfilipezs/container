<?php

declare(strict_types=1);

namespace Luizfilipezs\Container\Interfaces;

interface ReflectionClassStorageInterface
{
    public function create(string $className): \ReflectionClass;

    public function get(string $className): ?\ReflectionClass;

    public function getOrCreate(string $className): \ReflectionClass;

    public function has(string $className): bool;
}
