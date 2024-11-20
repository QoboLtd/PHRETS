<?php

use PHPUnit\Framework\TestCase;
use PHRETS\Configuration;
use PHRETS\Session;

class SessionTest extends TestCase
{
    /** @test **/
    public function itBuilds()
    {
        $c = new Configuration();
        $c->setLoginUrl('http://www.reso.org/login');

        $s = new Session($c);
        $this->assertSame($c, $s->getConfiguration());
    }

    /**
     * @test
     */
    public function itDetectsInvalidConfigurations()
    {
        $this->expectException(\PHRETS\Exceptions\MissingConfiguration::class);
        $c = new Configuration();
        $c->setLoginUrl('http://www.reso.org/login');

        $s = new Session($c);
        $s->Login();
    }

    /** @test **/
    public function itGivesBackTheLoginUrl()
    {
        $c = new Configuration();
        $c->setLoginUrl('http://www.reso.org/login');

        $s = new Session($c);

        $this->assertSame('http://www.reso.org/login', $s->getLoginUrl());
    }

    /** @test **/
    public function itTracksCapabilities()
    {
        $login_url = 'http://www.reso.org/login';
        $c = new Configuration();
        $c->setLoginUrl($login_url);

        $s = new Session($c);
        $capabilities = $s->getCapabilities();
        $this->assertInstanceOf('PHRETS\Capabilities', $capabilities);
        $this->assertSame($login_url, $capabilities->get('Login'));
    }

    /** @test **/
    public function itDisablesRedirectsWhenDesired()
    {
        $c = new Configuration();
        $c->setLoginUrl('http://www.reso.org/login');
        $c->setOption('disable_follow_location', true);

        $s = new Session($c);

        $this->assertFalse($s->getDefaultOptions()['allow_redirects']);
    }

    /** @test **/
    public function itUsesTheSetLogger()
    {
        $logger = $this->createMock(\Monolog\Logger::class);

        // expect that the string 'Context' will be changed into an array
        $logger->expects($this->atLeastOnce())->method('debug')->withConsecutive(
            [$this->anything()],
            [$this->equalTo('Message'), $this->equalTo(['Context'])]
        );

        $c = new Configuration();
        $c->setLoginUrl('http://www.reso.org/login');

        $s = new Session($c);
        $s->setLogger($logger);

        $s->debug('Message', 'Context');
    }

    /** @test **/
    public function itFixesTheLoggerContextAutomatically()
    {
        $logger = $this->createMock(\Monolog\Logger::class);
        // just expect that a debug message is spit out
        $logger->expects($this->atLeastOnce())->method('debug')->with($this->matchesRegularExpression('/logger/'));

        $c = new Configuration();
        $c->setLoginUrl('http://www.reso.org/login');

        $s = new Session($c);
        $s->setLogger($logger);
    }

    /** @test **/
    public function itLoadsACookieJar()
    {
        $c = new Configuration();
        $c->setLoginUrl('http://www.reso.org/login');

        $s = new Session($c);
        $this->assertInstanceOf('\GuzzleHttp\Cookie\CookieJarInterface', $s->getCookieJar());
    }

    /** @test **/
    public function itAllowsOverridingTheCookieJar()
    {
        $c = new Configuration();
        $c->setLoginUrl('http://www.reso.org/login');

        $s = new Session($c);

        $jar = new \GuzzleHttp\Cookie\CookieJar();
        $s->setCookieJar($jar);

        $this->assertSame($jar, $s->getCookieJar());
    }
}
