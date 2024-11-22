<?php

use PHPUnit\Framework\TestCase;
use PHRETS\Configuration;
use PHRETS\Strategies\SimpleStrategy;
use PHRETS\Strategies\Strategy;

class ConfigurationTest extends TestCase
{
    /** @test **/
    public function itDoesTheBasics()
    {
        $config = new Configuration();
        $config->setLoginUrl('http://www.reso.org/login'); // not a valid RETS server.  just using for testing
        $config->setUsername('user');
        $config->setPassword('pass');

        $this->assertSame('http://www.reso.org/login', $config->getLoginUrl());
        $this->assertSame('user', $config->getUsername());
        $this->assertSame('pass', $config->getPassword());
    }

    /** @test **/
    public function itLoadsConfigFromArray()
    {
        $config = Configuration::load([
            'login_url' => 'http://www.reso.org/login',
            'username' => 'user',
            'password' => 'pass',
        ]);

        $this->assertSame('http://www.reso.org/login', $config->getLoginUrl());
        $this->assertSame('user', $config->getUsername());
        $this->assertSame('pass', $config->getPassword());
    }

    /**
     * @test
     **/
    public function itComplainsAboutBadConfig()
    {
        $this->expectException(\PHRETS\Exceptions\InvalidConfiguration::class);
        Configuration::load();
    }

    /** @test **/
    public function itLoadsDefaultRetsVersion()
    {
        $config = new Configuration();
        $this->assertTrue($config->getRetsVersion()->is1_5());
    }

    /** @test **/
    public function itHandlesVersionsCorrectly()
    {
        $config = new Configuration();
        $config->setRetsVersion('1.7.2');
        $this->assertTrue($config->getRetsVersion()->is1_7_2());
    }

    /** @test **/
    public function itHandlesUserAgents()
    {
        $config = new Configuration();
        $config->setUserAgent('PHRETS/2.0');
        $this->assertSame('PHRETS/2.0', $config->getUserAgent());
    }

    /** @test **/
    public function itHandlesUaPasswords()
    {
        $config = new Configuration();
        $config->setUserAgent('PHRETS/2.0');
        $config->setUserAgentPassword('test12345');
        $this->assertSame('PHRETS/2.0', $config->getUserAgent());
        $this->assertSame('test12345', $config->getUserAgentPassword());
    }

    /** @test **/
    public function itTracksOptions()
    {
        $config = new Configuration();
        $config->setOption('param', true);
        $this->assertTrue($config->readOption('param'));
    }

    /** @test **/
    public function itLoadsAStrategy()
    {
        $config = new Configuration();
        $this->assertInstanceOf(Strategy::class, $config->getStrategy());
        $this->assertInstanceOf(SimpleStrategy::class, $config->getStrategy());
    }

    /** @test **/
    public function itAllowsOverridingTheStrategy()
    {
        $config = new Configuration();
        $strategy = new SimpleStrategy();
        $strategy->initialize($config);
        $config->setStrategy($strategy);

        $this->assertSame($strategy, $config->getStrategy());
    }

    /** @test **/
    public function itGeneratesUserAgentAuthHashesCorrectly()
    {
        $c = new Configuration();
        $c->setLoginUrl('http://www.reso.org/login')
            ->setUserAgent('PHRETS/2.0')
            ->setUserAgentPassword('12345')
            ->setRetsVersion('1.7.2');

        $s = new \PHRETS\Session($c);
        $this->assertSame('123c96e02e514da469db6bc61ab998dc', $c->userAgentDigestHash($s));
    }

    /** @test **/
    public function itKeepsDigestAsTheDefault()
    {
        $c = new Configuration();
        $this->assertSame(Configuration::AUTH_DIGEST, $c->getHttpAuthenticationMethod());
    }

    /**
     * @test
     **/
    public function itDoesntAllowBogusAuthMethods()
    {
        $this->expectException(InvalidArgumentException::class);
        $c = new Configuration();
        $c->setHttpAuthenticationMethod('bogus');
    }

    /** @test **/
    public function itAcceptsBasicAuth()
    {
        $c = new Configuration();
        $c->setHttpAuthenticationMethod(Configuration::AUTH_BASIC);
        $this->assertSame(Configuration::AUTH_BASIC, $c->getHttpAuthenticationMethod());
    }
}
