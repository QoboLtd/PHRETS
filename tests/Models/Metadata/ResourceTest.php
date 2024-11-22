<?php
namespace PHRETS\Test\Models\Metadata;

use BadMethodCallException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Models\Metadata\Resource;

class ResourceTest extends TestCase
{
    #[Test]
    public function itHolds(): void
    {
        $metadata = new Resource();
        $metadata->setDescription('Test Description');

        $this->assertSame('Test Description', $metadata->getDescription());
    }

    #[Test]
    public function itDoesntLikeBadMethods(): void
    {
        $this->expectException(BadMethodCallException::class);
        $metadata = new Resource();
        // @phpstan-ignore-next-line method.notFound
        $metadata->totallyBogus();
    }

    #[Test]
    public function itReturnsNullForUnrecognizedAttributes(): void
    {
        $metadata = new Resource();
        // @phpstan-ignore-next-line method.notFound
        $this->assertNull($metadata->getSomethingFake());
    }

    #[Test]
    public function itWorksLikeAnArray(): void
    {
        $metadata = new Resource();
        $metadata->setDescription('Test Description');

        $this->assertTrue(isset($metadata['Description']));
        $this->assertSame('Test Description', $metadata['Description']);
    }

    #[Test]
    public function itSetsLikeAnArray(): void
    {
        $metadata = new Resource();
        $metadata['Description'] = 'Array setter';

        $this->assertSame('Array setter', $metadata->getDescription());

        unset($metadata['Description']);

        $this->assertNull($metadata->getDescription());
    }
}
