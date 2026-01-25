<?php

declare(strict_types=1);

namespace Luizfilipezs\Container\Interfaces;

/**
 * ReflectionClass storage interface.
 */
interface ReflectionClassStorageInterface
{
    /**
     * Creates a new ReflectionClass and stores it.
     *
     * @param string $className Class name.
     *
     * @return \ReflectionClass Class reflection stored.
     *
     * @throws \InvalidArgumentException If the class has already been added.
     */
    public function create(string $className): \ReflectionClass;

    /**
     * Retrieves a stored ReflectionClass.
     *
     * @param string $className Class name.
     *
     * @return ?\ReflectionClass Class reflection stored.
     */
    public function get(string $className): ?\ReflectionClass;

    /**
     * Retrieves a stored ReflectionClass or creates a new one.
     *
     * @param string $className Class name.
     *
     * @return \ReflectionClass Class reflection stored.
     */
    public function getOrCreate(string $className): \ReflectionClass;

    /**
     * Checks if a ReflectionClass is stored.
     *
     * @param string $className Class name.
     *
     * @return bool If ReflectionClass is stored.
     */
    public function has(string $className): bool;
}
