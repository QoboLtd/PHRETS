<?php
namespace PHRETS\Test\Integration;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;
use PHRETS\Configuration;
use PHRETS\Enums\RETSVersion;
use PHRETS\Session;
use Psr\Http\Message\RequestInterface;

class BaseIntegration extends TestCase
{
    protected ClientInterface $client;
    protected ?Session $session;

    /** @var list<string> */
    protected array $search_select = [
        'LIST_0', 'LIST_1', 'LIST_5', 'LIST_106', 'LIST_105', 'LIST_15', 'LIST_22', 'LIST_10', 'LIST_30',
    ];

    private string $path;

    /** @var array<string,string> */
    private array $ignored_headers = [
        'ACCEPT' => 'Accept',
        'USER-AGENT' => 'User-Agent',
        'COOKIE' => 'Cookie',
    ];

    public function setUp(): void
    {
        $this->path = __DIR__ . '/Fixtures/Http';
        $config = new Configuration(version: RETSVersion::VERSION_1_7_2);
        $config->setLoginUrl('http://retsgw.flexmls.com/rets2_1/Login')
                ->setUsername(getenv('PHRETS_TESTING_USERNAME'))
                ->setPassword(getenv('PHRETS_TESTING_PASSWORD'));

        $this->session = $this->createSession($config);
        $this->session->Login();
    }

    protected function createSession(Configuration $config): Session
    {
        $new_client = new Client(['handler' => $this->createHandler()]);
        return new Session($config, $new_client);
    }

    /** @return list<string> */
    public function getIgnoredHeaders(): array
    {
        return array_values($this->ignored_headers);
    }

    public function addIgnoredHeader(string $name): self
    {
        $this->ignored_headers[strtoupper($name)] = $name;

        return $this;
    }

    public function createHandler(): HandlerStack
    {
        $stack = HandlerStack::create();

        $stack->push($this->onBefore());
        $stack->push($this->onComplete());

        return $stack;
    }

    public function onBefore(): callable
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $promise = $handler($request, $options);

                if (file_exists($this->getFullFilePath($request))) {
                    $responsedata = file_get_contents($this->getFullFilePath($request));
                    $response = \GuzzleHttp\Psr7\Message::parseResponse($responsedata);
                    $promise->resolve($response);
                }

                return $promise;
            };
        };
    }

    public function onComplete(): callable
    {
        return function (callable $handler) {
            return function ($request, array $options) use ($handler) {
                return $handler($request, $options)->then(
                    function ($response) use ($request) {
                        if (!file_exists($this->getPath($request))) {
                            mkdir($this->getPath($request), 0777, true);
                        }

                        if (!file_exists($this->getFullFilePath($request))) {
                            file_put_contents($this->getFullFilePath($request), \GuzzleHttp\Psr7\Message::toString($response));
                        }

                        return $response;
                    }
                );
            };
        };
    }

    protected function getPath(RequestInterface $request): string
    {
        $path = $this->path . DIRECTORY_SEPARATOR . strtolower($request->getMethod()) . DIRECTORY_SEPARATOR . $request->getUri()->getHost() . DIRECTORY_SEPARATOR;

        if ($request->getRequestTarget() !== '/') {
            $rpath = $request->getUri()->getPath();
            $rpath = (substr($rpath, 0, 1) === '/') ? substr($rpath, 1) : $rpath;
            $rpath = (substr($rpath, -1, 1) === '/') ? substr($rpath, 0, -1) : $rpath;

            $path .= str_replace('/', '_', $rpath) . DIRECTORY_SEPARATOR;
        }

        return $path;
    }

    protected function getFileName(RequestInterface $request): string
    {
        $result = trim($request->getMethod() . ' ' . $request->getRequestTarget())
            . ' HTTP/' . $request->getProtocolVersion();
        foreach ($request->getHeaders() as $name => $values) {
            if (array_key_exists(strtoupper($name), $this->ignored_headers)) {
                continue;
            }
            $result .= "\r\n{$name}: " . implode(', ', $values);
        }

        $request = $result . "\r\n\r\n" . $request->getBody();

        return md5((string) $request) . '.txt';
    }

    protected function getFullFilePath(RequestInterface $request): string
    {
        return $this->getPath($request) . $this->getFileName($request);
    }
}
