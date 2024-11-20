<?php

use PHPUnit\Framework\TestCase;
use PHRETS\Capabilities;

class CapabilitiesTest extends TestCase
{
    /** @test **/
    public function itTracks()
    {
        $cpb = new Capabilities();
        $cpb->add('login', 'http://www.reso.org/login');

        $this->assertNotNull($cpb->get('login'));
        $this->assertNull($cpb->get('test'));
    }

    /**
     * @test
     * **/
    public function itBarfsWhenNotGivenEnoughInformationToBuildAbsoluteUrls()
    {
        $this->expectException(InvalidArgumentException::class);
        $cpb = new Capabilities();
        $cpb->add('Login', '/rets/Login');
    }

    /** @test **/
    public function itCanBuildAbsoluteUrlsFromRelativeOnes()
    {
        $cpb = new Capabilities();
        $cpb->add('Login', 'http://www.google.com/login');

        $cpb->add('Search', '/search');
        $this->assertSame('http://www.google.com:80/search', $cpb->get('Search'));
    }

    /** @test **/
    public function itPreservesExplicityPorts()
    {
        $cpb = new Capabilities();
        $cpb->add('Login', 'http://www.google.com:8080/login');

        $cpb->add('Search', '/search');
        $this->assertSame('http://www.google.com:8080/search', $cpb->get('Search'));
    }
}
