<?php

declare(strict_types=1);

namespace Luizfilipezs\Container\Tests\Unit\Services;

use InvalidArgumentException;
use Luizfilipezs\Container\Services\ReflectionClassStorage;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ReflectionClassStorageTest extends TestCase
{
    private ReflectionClassStorage $storage;

    protected function setUp(): void
    {
        $this->storage = new ReflectionClassStorage();
    }

    public function testAddStoresAndReturnsReflectionClass(): void
    {
        $reflection = $this->storage->add(\stdClass::class);

        $this->assertInstanceOf(ReflectionClass::class, $reflection);
        $this->assertSame(\stdClass::class, $reflection->getName());
        $this->assertTrue($this->storage->has(\stdClass::class));
    }

    public function testAddThrowsExceptionWhenClassIsAlreadyAdded(): void
    {
        $this->storage->add(\stdClass::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\stdClass::class . ' has already been added.');

        $this->storage->add(\stdClass::class);
    }

    public function testGetReturnsReflectionClassWhenExists(): void
    {
        $added = $this->storage->add(\stdClass::class);

        $retrieved = $this->storage->get(\stdClass::class);

        $this->assertSame($added, $retrieved);
    }

    public function testGetReturnsNullWhenClassDoesNotExist(): void
    {
        $this->assertNull(
            $this->storage->get(\stdClass::class),
        );
    }

    public function testHasReturnsTrueWhenClassExists(): void
    {
        $this->storage->add(\stdClass::class);

        $this->assertTrue(
            $this->storage->has(\stdClass::class),
        );
    }

    public function testHasReturnsFalseWhenClassDoesNotExist(): void
    {
        $this->assertFalse(
            $this->storage->has(\stdClass::class),
        );
    }

    public function testGetOrAddAddsAndReturnsReflectionClassWhenNotExists(): void
    {
        $reflection = $this->storage->getOrAdd(\stdClass::class);

        $this->assertInstanceOf(ReflectionClass::class, $reflection);
        $this->assertSame(\stdClass::class, $reflection->getName());
        $this->assertTrue($this->storage->has(\stdClass::class));
    }

    public function testGetOrAddReturnsExistingReflectionClassWhenAlreadyAdded(): void
    {
        $first = $this->storage->add(\stdClass::class);
        $second = $this->storage->getOrAdd(\stdClass::class);

        $this->assertSame($first, $second);
    }
}
