<?php
namespace PHRETS\Test;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Capabilities;

class CapabilitiesTest extends TestCase
{
    #[Test]
    public function itTracks(): void
    {
        $cpb = new Capabilities();
        $cpb->add('login', 'http://www.reso.org/login');

        $this->assertNotNull($cpb->get('login'));
        $this->assertNull($cpb->get('test'));
    }

    #[Test]
    public function itBarfsWhenNotGivenEnoughInformationToBuildAbsoluteUrls(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $cpb = new Capabilities();
        $cpb->add('Login', '/rets/Login');
    }

    #[Test]
    public function itCanBuildAbsoluteUrlsFromRelativeOnes(): void
    {
        $cpb = new Capabilities();
        $cpb->add('Login', 'http://www.google.com/login');

        $cpb->add('Search', '/search');
        $this->assertSame('http://www.google.com:80/search', $cpb->get('Search'));
    }

    #[Test]
    public function itPreservesExplicityPorts(): void
    {
        $cpb = new Capabilities();
        $cpb->add('Login', 'http://www.google.com:8080/login');

        $cpb->add('Search', '/search');
        $this->assertSame('http://www.google.com:8080/search', $cpb->get('Search'));
    }

    public function testBoolCapability(): void
    {
        $caps = new Capabilities();
        $caps->add('BoolFlag', true);

        $this->assertSame(true, $caps->get('BoolFlag'));
    }

    public function testIntCapability(): void
    {
        $caps = new Capabilities();
        $caps->add('IntFlag', 256);

        $this->assertSame(256, $caps->get('IntFlag'));
    }
}
