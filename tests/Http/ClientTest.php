<?php

use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /** @test **/
    public function itMakes()
    {
        $this->assertInstanceOf('GuzzleHttp\\Client', \PHRETS\Http\Client::make());
    }

    /** @test **/
    public function itAllowsOverrides()
    {
        $gc = new GuzzleHttp\Client();
        \PHRETS\Http\Client::set($gc);

        $this->assertSame($gc, \PHRETS\Http\Client::make());
    }
}
