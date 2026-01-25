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

    public function testCreateStoresAndReturnsReflectionClass(): void
    {
        $reflection = $this->storage->create(\stdClass::class);

        $this->assertInstanceOf(ReflectionClass::class, $reflection);
        $this->assertSame(\stdClass::class, $reflection->getName());
        $this->assertTrue($this->storage->has(\stdClass::class));
    }

    public function testCreateThrowsExceptionWhenClassIsAlreadyCreateed(): void
    {
        $this->storage->create(\stdClass::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\stdClass::class . ' has already been createed.');

        $this->storage->create(\stdClass::class);
    }

    public function testGetReturnsReflectionClassWhenExists(): void
    {
        $createed = $this->storage->create(\stdClass::class);

        $retrieved = $this->storage->get(\stdClass::class);

        $this->assertSame($createed, $retrieved);
    }

    public function testGetReturnsNullWhenClassDoesNotExist(): void
    {
        $this->assertNull(
            $this->storage->get(\stdClass::class),
        );
    }

    public function testHasReturnsTrueWhenClassExists(): void
    {
        $this->storage->create(\stdClass::class);

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

    public function testGetOrCreateCreatesAndReturnsReflectionClassWhenNotExists(): void
    {
        $reflection = $this->storage->getOrCreate(\stdClass::class);

        $this->assertInstanceOf(ReflectionClass::class, $reflection);
        $this->assertSame(\stdClass::class, $reflection->getName());
        $this->assertTrue($this->storage->has(\stdClass::class));
    }

    public function testGetOrCreateReturnsExistingReflectionClassWhenAlreadyCreateed(): void
    {
        $first = $this->storage->create(\stdClass::class);
        $second = $this->storage->getOrCreate(\stdClass::class);

        $this->assertSame($first, $second);
    }
}
