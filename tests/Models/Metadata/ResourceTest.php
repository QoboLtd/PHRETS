<?php

use PHPUnit\Framework\TestCase;
use PHRETS\Models\Metadata\Resource;

class ResourceTest extends TestCase
{
    /** @test **/
    public function itHolds()
    {
        $metadata = new Resource();
        $metadata->setDescription('Test Description');

        $this->assertSame('Test Description', $metadata->getDescription());
    }

    /**
     * @test
     **/
    public function itDoesntLikeBadMethods()
    {
        $this->expectException(BadMethodCallException::class);
        $metadata = new Resource();
        $metadata->totallyBogus();
    }

    /** @test **/
    public function itReturnsNullForUnrecognizedAttributes()
    {
        $metadata = new Resource();
        $this->assertNull($metadata->getSomethingFake());
    }

    /** @test **/
    public function itWorksLikeAnArray()
    {
        $metadata = new Resource();
        $metadata->setDescription('Test Description');

        $this->assertTrue(isset($metadata['Description']));
        $this->assertSame('Test Description', $metadata['Description']);
    }

    /** @test **/
    public function itSetsLikeAnArray()
    {
        $metadata = new Resource();
        $metadata['Description'] = 'Array setter';

        $this->assertSame('Array setter', $metadata->getDescription());

        unset($metadata['Description']);

        $this->assertNull($metadata->getDescription());
    }
}
