<?php

use PHPUnit\Framework\TestCase;
use PHRETS\Interpreters\GetObject;

class GetObjectTest extends TestCase
{
    /** @test **/
    public function itCombinesSingles()
    {
        $this->assertEquals(['12345:1'], GetObject::ids(12345, 1));
    }

    /** @test **/
    public function itCombinesMultipleFromString()
    {
        $this->assertEquals(['12345:1', '67890:1'], GetObject::ids('12345,67890', 1));
    }

    /** @test **/
    public function itCombinesMultipleFromColonString()
    {
        $this->assertEquals(['12345:1', '67890:1'], GetObject::ids('12345:67890', 1));
    }

    /** @test **/
    public function itCombinesMultipleFromArray()
    {
        $this->assertEquals(['12345:1', '67890:1'], GetObject::ids([12345, 67890], 1));
    }

    /** @test **/
    public function itCombinesMultipleObjectIdStrings()
    {
        $this->assertEquals(['12345:1:2:3', '67890:1:2:3'], GetObject::ids([12345, 67890], '1,2,3'));
    }

    /** @test **/
    public function itCombinesMultipleObjectIdArrays()
    {
        $this->assertEquals(['12345:1:2:3', '67890:1:2:3'], GetObject::ids([12345, 67890], [1, 2, 3]));
    }

    /** @test **/
    public function itParsesRanges()
    {
        $this->assertEquals(['12345:1:2:3:4:5', '67890:1:2:3:4:5'], GetObject::ids([12345, 67890], '1-5'));
    }
}
