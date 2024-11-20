<?php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;
use PHRETS\Session;
use Psr\Http\Message\RequestInterface;

class BaseIntegration extends TestCase
{
    protected $client;
    /** @var Session */
    protected $session;
    protected $search_select = [
        'LIST_0', 'LIST_1', 'LIST_5', 'LIST_106', 'LIST_105', 'LIST_15', 'LIST_22', 'LIST_10', 'LIST_30',
    ];

    private $path;
    private $ignored_headers = [
        'ACCEPT' => 'Accept',
        'USER-AGENT' => 'User-Agent',
        'COOKIE' => 'Cookie',
    ];

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->path = __DIR__ . '/Fixtures/Http';
    }

    public function setUp(): void
    {
        $config = new \PHRETS\Configuration();
        $config->setLoginUrl('http://retsgw.flexmls.com/rets2_1/Login')
                ->setUsername(getenv('PHRETS_TESTING_USERNAME'))
                ->setPassword(getenv('PHRETS_TESTING_PASSWORD'))
                ->setRetsVersion('1.7.2');

        $this->session = new PHRETS\Session($config);
        $client = $this->session->getClient();

        $defaults = $client->getConfig();
        $new_client = new GuzzleHttp\Client($defaults);

        PHRETS\Http\Client::set($new_client);

        $this->attachTo($new_client);

        $this->session->Login();
    }

    public function getIgnoredHeaders()
    {
        return array_values($this->ignored_headers);
    }

    public function addIgnoredHeader($name)
    {
        $this->ignored_headers[strtoupper($name)] = $name;

        return $this;
    }

    public function attachTo(Client $client)
    {
        /** @var HandlerStack $stack */
        $stack = $client->getConfig('handler');
        $stack->push($this->onBefore());
        $stack->push($this->onComplete());
    }

    public function onBefore()
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

    public function onComplete()
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

    protected function getPath(RequestInterface $request)
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

    protected function getFileName(RequestInterface $request)
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
        $hash = md5((string) $request) . '.txt';

        return $hash;
    }

    protected function getFullFilePath(RequestInterface $request)
    {
        $fullFilePath = $this->getPath($request) . $this->getFileName($request);

        return $fullFilePath;
    }
}
