<?php
namespace PHRETS\Test;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Configuration;
use PHRETS\Enums\RETSVersion;
use PHRETS\Session;
use PHRETS\Strategies\SimpleStrategy;

class ConfigurationTest extends TestCase
{
    #[Test]
    public function itDoesTheBasics(): void
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
    public function itLoadsConfigFromArray(): void
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
    public function itComplainsAboutBadConfig(): void
    {
        $this->expectException(\PHRETS\Exceptions\InvalidConfiguration::class);
        Configuration::load();
    }

    #[Test]
    public function itLoadsDefaultRetsVersion(): void
    {
        $config = new Configuration();
        $this->assertSame(RETSVersion::VERSION_1_5, $config->getRetsVersion());
    }

    #[Test]
    public function itHandlesVersionsCorrectly(): void
    {
        $config = new Configuration(version: RETSVersion::VERSION_1_7_2);
        $this->assertSame(RETSVersion::VERSION_1_7_2, $config->getRetsVersion());
    }

    #[Test]
    public function itHandlesUserAgents(): void
    {
        $config = new Configuration();
        $config->setUserAgent('PHRETS/2.0');
        $this->assertSame('PHRETS/2.0', $config->getUserAgent());
    }

    #[Test]
    public function itHandlesUaPasswords(): void
    {
        $config = new Configuration();
        $config->setUserAgent('PHRETS/2.0');
        $config->setUserAgentPassword('test12345');
        $this->assertSame('PHRETS/2.0', $config->getUserAgent());
        $this->assertSame('test12345', $config->getUserAgentPassword());
    }

    #[Test]
    public function itTracksOptions(): void
    {
        $config = new Configuration();
        $config->setOption('param', true);
        $this->assertTrue($config->readOption('param'));
    }

    #[Test]
    public function itLoadsAStrategy(): void
    {
        $config = new Configuration();
        $this->assertInstanceOf(SimpleStrategy::class, $config->getStrategy());
    }

    #[Test]
    public function itAllowsOverridingTheStrategy(): void
    {
        $strategy = new SimpleStrategy();
        $config = new Configuration($strategy);
        $this->assertSame($strategy, $config->getStrategy());
    }

    #[Test]
    public function itGeneratesUserAgentAuthHashesCorrectly(): void
    {
        $c = new Configuration(version: RETSVersion::VERSION_1_7_2);
        $c->setLoginUrl('http://www.reso.org/login')
            ->setUserAgent('PHRETS/2.0')
            ->setUserAgentPassword('12345');

        $s = new Session($c);
        $this->assertSame('123c96e02e514da469db6bc61ab998dc', $c->userAgentDigestHash($s));
    }

    #[Test]
    public function itKeepsDigestAsTheDefault(): void
    {
        $c = new Configuration();
        $this->assertSame(Configuration::AUTH_DIGEST, $c->getHttpAuthenticationMethod());
    }

    #[Test]
    public function itDoesntAllowBogusAuthMethods(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $c = new Configuration();
        $c->setHttpAuthenticationMethod('bogus');
    }

    #[Test]
    public function itAcceptsBasicAuth(): void
    {
        $c = new Configuration();
        $c->setHttpAuthenticationMethod(Configuration::AUTH_BASIC);
        $this->assertSame(Configuration::AUTH_BASIC, $c->getHttpAuthenticationMethod());
    }
}
