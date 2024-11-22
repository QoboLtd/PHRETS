<?php
namespace PHRETS\Test;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Capabilities;

class CapabilitiesTest extends TestCase
{
    #[Test]
    public function itTracks()
    {
        $cpb = new Capabilities();
        $cpb->add('login', 'http://www.reso.org/login');

        $this->assertNotNull($cpb->get('login'));
        $this->assertNull($cpb->get('test'));
    }

    #[Test]
    public function itBarfsWhenNotGivenEnoughInformationToBuildAbsoluteUrls()
    {
        $this->expectException(InvalidArgumentException::class);
        $cpb = new Capabilities();
        $cpb->add('Login', '/rets/Login');
    }

    #[Test]
    public function itCanBuildAbsoluteUrlsFromRelativeOnes()
    {
        $cpb = new Capabilities();
        $cpb->add('Login', 'http://www.google.com/login');

        $cpb->add('Search', '/search');
        $this->assertSame('http://www.google.com:80/search', $cpb->get('Search'));
    }

    #[Test]
    public function itPreservesExplicityPorts()
    {
        $cpb = new Capabilities();
        $cpb->add('Login', 'http://www.google.com:8080/login');

        $cpb->add('Search', '/search');
        $this->assertSame('http://www.google.com:8080/search', $cpb->get('Search'));
    }
}
