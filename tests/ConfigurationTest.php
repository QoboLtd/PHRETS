<?php
namespace PHRETS\Test;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Configuration;
use PHRETS\Session;
use PHRETS\Strategies\SimpleStrategy;
use PHRETS\Strategies\Strategy;

class ConfigurationTest extends TestCase
{
    #[Test]
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

    #[Test]
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

    #[Test]
    public function itComplainsAboutBadConfig()
    {
        $this->expectException(\PHRETS\Exceptions\InvalidConfiguration::class);
        Configuration::load();
    }

    #[Test]
    public function itLoadsDefaultRetsVersion()
    {
        $config = new Configuration();
        $this->assertTrue($config->getRetsVersion()->is1_5());
    }

    #[Test]
    public function itHandlesVersionsCorrectly()
    {
        $config = new Configuration();
        $config->setRetsVersion('1.7.2');
        $this->assertTrue($config->getRetsVersion()->is1_7_2());
    }

    #[Test]
    public function itHandlesUserAgents()
    {
        $config = new Configuration();
        $config->setUserAgent('PHRETS/2.0');
        $this->assertSame('PHRETS/2.0', $config->getUserAgent());
    }

    #[Test]
    public function itHandlesUaPasswords()
    {
        $config = new Configuration();
        $config->setUserAgent('PHRETS/2.0');
        $config->setUserAgentPassword('test12345');
        $this->assertSame('PHRETS/2.0', $config->getUserAgent());
        $this->assertSame('test12345', $config->getUserAgentPassword());
    }

    #[Test]
    public function itTracksOptions()
    {
        $config = new Configuration();
        $config->setOption('param', true);
        $this->assertTrue($config->readOption('param'));
    }

    #[Test]
    public function itLoadsAStrategy()
    {
        $config = new Configuration();
        $this->assertInstanceOf(Strategy::class, $config->getStrategy());
        $this->assertInstanceOf(SimpleStrategy::class, $config->getStrategy());
    }

    #[Test]
    public function itAllowsOverridingTheStrategy()
    {
        $strategy = new SimpleStrategy();
        $config = new Configuration($strategy);
        $this->assertSame($strategy, $config->getStrategy());
    }

    #[Test]
    public function itGeneratesUserAgentAuthHashesCorrectly()
    {
        $c = new Configuration();
        $c->setLoginUrl('http://www.reso.org/login')
            ->setUserAgent('PHRETS/2.0')
            ->setUserAgentPassword('12345')
            ->setRetsVersion('1.7.2');

        $s = new Session($c);
        $this->assertSame('123c96e02e514da469db6bc61ab998dc', $c->userAgentDigestHash($s));
    }

    #[Test]
    public function itKeepsDigestAsTheDefault()
    {
        $c = new Configuration();
        $this->assertSame(Configuration::AUTH_DIGEST, $c->getHttpAuthenticationMethod());
    }

    #[Test]
    public function itDoesntAllowBogusAuthMethods()
    {
        $this->expectException(InvalidArgumentException::class);
        $c = new Configuration();
        $c->setHttpAuthenticationMethod('bogus');
    }

    #[Test]
    public function itAcceptsBasicAuth()
    {
        $c = new Configuration();
        $c->setHttpAuthenticationMethod(Configuration::AUTH_BASIC);
        $this->assertSame(Configuration::AUTH_BASIC, $c->getHttpAuthenticationMethod());
    }
}
