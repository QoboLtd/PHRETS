<?php
namespace PHRETS\Test;

use GuzzleHttp\Cookie\CookieJar;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Configuration;
use PHRETS\Session;
use Psr\Log\LoggerInterface;

class SessionTest extends TestCase
{
    #[Test]
    public function itBuilds(): void
    {
        $c = new Configuration();
        $c->setLoginUrl('http://www.reso.org/login');

        $s = new Session($c);
        $this->assertSame($c, $s->getConfiguration());
    }

    #[Test]
    public function itDetectsInvalidConfigurations(): void
    {
        $this->expectException(\PHRETS\Exceptions\MissingConfiguration::class);
        $c = new Configuration();
        $c->setLoginUrl('http://www.reso.org/login');

        $s = new Session($c);
        $s->Login();
    }

    #[Test]
    public function itGivesBackTheLoginUrl(): void
    {
        $c = new Configuration();
        $c->setLoginUrl('http://www.reso.org/login');

        $s = new Session($c);

        $this->assertSame('http://www.reso.org/login', $s->getLoginUrl());
    }

    #[Test]
    public function itTracksCapabilities(): void
    {
        $login_url = 'http://www.reso.org/login';
        $c = new Configuration();
        $c->setLoginUrl($login_url);

        $s = new Session($c);
        $capabilities = $s->getCapabilities();
        $this->assertSame($login_url, $capabilities->get('Login'));
    }

    #[Test]
    public function itDisablesRedirectsWhenDesired(): void
    {
        $c = new Configuration();
        $c->setLoginUrl('http://www.reso.org/login');
        $c->setOption('disable_follow_location', true);

        $s = new Session($c);

        $defaultOptions = $s->getDefaultOptions();
        $this->assertArrayHasKey('allow_redirects', $defaultOptions);
        $this->assertFalse($defaultOptions['allow_redirects']);
    }

    #[Test]
    public function itUsesTheSetLogger(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->setConstructorArgs(['TEST'])
            ->onlyMethods(['debug'])->getMock();

        $c = new Configuration();
        $c->setLoginUrl('http://www.reso.org/login');

        $s = new Session($c, logger: $logger);

        $count = 0;
        $messages = [
            'Message',
            'Context',
        ];

        $logger->expects($this->any())->method('debug')->willReturnCallback(
            function ($message) use (&$count, $messages): void {
                self::assertSame($messages[$count], $message);
                $count++;
            }
        );

        $s->debug('Message', 'Context');
    }

    #[Test]
    public function itFixesTheLoggerContextAutomatically(): void
    {
        $logger = $this->createMock(\Monolog\Logger::class);
        assert($logger instanceof LoggerInterface);
        // just expect that a debug message is spit out
        $logger->expects($this->atLeastOnce())->method('debug')->with($this->matchesRegularExpression('/logger/'));

        $c = new Configuration();
        $c->setLoginUrl('http://www.reso.org/login');

        $s = new Session($c, logger: $logger);
    }

    #[Test]
    public function itLoadsACookieJar(): void
    {
        $c = new Configuration();
        $c->setLoginUrl('http://www.reso.org/login');

        $s = new Session($c);
        $this->assertInstanceOf(CookieJar::class, $s->getCookieJar());
    }

    #[Test]
    public function itAllowsOverridingTheCookieJar(): void
    {
        $c = new Configuration();
        $c->setLoginUrl('http://www.reso.org/login');


        $jar = new \GuzzleHttp\Cookie\CookieJar();
        $s = new Session($c, cookieJar: $jar);
        $this->assertSame($jar, $s->getCookieJar());
    }
}
