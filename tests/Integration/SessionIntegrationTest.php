<?php
namespace PHRETS\Test\Integration;

use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use PHPUnit\Framework\Attributes\Test;
use PHRETS\Configuration;
use PHRETS\Enums\RETSVersion;
use PHRETS\Session;

class SessionIntegrationTest extends BaseIntegration
{
    #[Test]
    public function itLogsIn(): void
    {
        assert($this->session !== null);
        $connect = $this->session->Login();
        $this->assertNull($connect->getBody());
    }

    #[Test]
    public function itMadeTheRequest(): void
    {
        $this->session->Login();
        $this->assertSame('http://retsgw.flexmls.com:80/rets2_1/Login', $this->session->getLastRequestURL());
    }

    #[Test]
    public function itThrowsAnExceptionWhenMakingABadRequest(): void
    {
        $this->expectException(\PHRETS\Exceptions\RETSException::class);
        $this->session->Login();

        $this->session->Search('Property', 'Z', '*'); // no such class by that name
    }

    #[Test]
    public function itTracksTheLastResponseBody(): void
    {
        $this->session->Login();

        // find something in the login response that we can count on
        $this->assertMatchesRegularExpression('/NotificationFeed/', $this->session->getLastResponse());
    }

    #[Test]
    public function itDisconnects(): void
    {
        $this->session->Login();

        $this->assertTrue($this->session->Disconnect());
    }

    #[Test]
    public function itRequestsTheServersActionTransaction(): void
    {
        $config = new \PHRETS\Configuration(version: RETSVersion::VERSION_1_7_2);

        // this endpoint doesn't actually exist, but the response is mocked, so...
        $config->setLoginUrl('http://retsgw.flexmls.com/action/rets2_1/Login')
                ->setUsername(getenv('PHRETS_TESTING_USERNAME'))
                ->setPassword(getenv('PHRETS_TESTING_PASSWORD'));

        $session = $this->createSession($config);
        $bulletin = $session->Login();

        $this->assertMatchesRegularExpression('/found an Action/', $bulletin->getBody());
    }

    #[Test]
    public function itUsesHttpPostMethodWhenDesired(): void
    {
        $config = new \PHRETS\Configuration(version: RETSVersion::VERSION_1_7_2);

        // this endpoint doesn't actually exist, but the response is mocked, so...
        $config->setLoginUrl('http://retsgw.flexmls.com/rets2_1/Login')
                ->setUsername(getenv('PHRETS_TESTING_USERNAME'))
                ->setPassword(getenv('PHRETS_TESTING_PASSWORD'))
                ->setOption('use_post_method', true);

        $session = $this->createSession($config);
        $session->Login();

        $system = $session->GetSystemMetadata();
        $this->assertSame('demomls', $system->getSystemID());

        $results = $session->Search('Property', 'A', '*', ['Limit' => 1, 'Select' => 'LIST_1']);
        $this->assertCount(1, $results);
    }

    #[Test]
    public function itTracksAGivenSessionId(): void
    {
        $this->session->Login();

        // mocked request to give back a session ID
        $this->session->GetTableMetadata('Property', 'RETSSESSIONID');

        $this->assertSame('21AC8993DC98DDCE648423628ECF4AB5', $this->session->getRetsSessionId());
    }

    #[Test]
    public function itDetectsWhenToUseUserAgentAuthentication(): void
    {
        $config = new Configuration(version: RETSVersion::VERSION_1_7_2);

        $config->setLoginUrl('http://retsgw.flexmls.com/rets2_1/Login')
                ->setUsername(getenv('PHRETS_TESTING_USERNAME'))
                ->setPassword(getenv('PHRETS_TESTING_PASSWORD'))
                ->setUserAgent('PHRETS/2.0')
                ->setUserAgentPassword('bogus_password');

        $handler = $this->createHandler();
        $session = new Session($config, new Client(['handler' => $handler]));

        /**
         * Attach a history container to Guzzle so we can verify the needed header is sent.
         */
        $container = [];

        $history = Middleware::history($container);
        $handler->push($history);

        $session->Login();

        $this->assertCount(1, $container);
        $last_request = $container[count($container) - 1];
        $this->assertMatchesRegularExpression('/Digest/', implode(', ', $last_request['request']->getHeader('RETS-UA-Authorization')));
        $this->assertArrayHasKey('Accept', $last_request['request']->getHeaders());
    }

    #[Test]
    public function itDoesntAllowRequestsToUnsupportedCapabilities(): void
    {
        $this->expectException(\PHRETS\Exceptions\CapabilityUnavailable::class);
        $config = new Configuration(version: RETSVersion::VERSION_1_7_2);

        // fake, mocked endpoint
        $config->setLoginUrl('http://retsgw.flexmls.com/limited/rets2_1/Login')
                ->setUsername(getenv('PHRETS_TESTING_USERNAME'))
                ->setPassword(getenv('PHRETS_TESTING_PASSWORD'));

        $session = $this->createSession($config);
        $session->Login();

        // make a request for metadata to a server that doesn't support metadata
        $session->GetSystemMetadata();
    }

    public function testDetailsAreAvailableFromLogin(): void
    {
        $connect = $this->session->Login();
        $this->assertSame('UNKNOWN', $connect->getMemberName());
        $this->assertNotNull($connect->getMetadataVersion());
    }
}
