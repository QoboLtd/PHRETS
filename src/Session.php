<?php

namespace PHRETS;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Exception\ClientException;
use PHRETS\Exceptions\CapabilityUnavailable;
use PHRETS\Exceptions\MissingConfiguration;
use PHRETS\Exceptions\RETSException;
use PHRETS\Http\ClientBuilder;
use PHRETS\Http\Response;
use PHRETS\Interpreters\GetObject;
use PHRETS\Interpreters\Search;
use PHRETS\Models\BaseObject;
use PHRETS\Models\Bulletin;
use PHRETS\Models\Search\Results;
use PHRETS\Parsers\ParserType;
use Psr\Log\LoggerInterface;
use Stringable;

class Session
{
    protected readonly ClientInterface $client;
    protected readonly CookieJarInterface $cookie_jar;
    protected readonly Capabilities $capabilities;
    protected readonly ?LoggerInterface $logger;

    protected ?string $rets_session_id = null;
    protected ?string $last_request_url;
    protected ?Response $last_response = null;

    /**
     * @throws \PHRETS\Exceptions\MissingConfiguration
     */
    public function __construct(
        protected readonly Configuration $configuration,
        ?ClientInterface $client = null,
        ?CookieJarInterface $cookieJar = null,
        ?LoggerInterface $logger = null
    ) {
        $loginUrl = $configuration->getLoginUrl();
        if ($loginUrl === null) {
            throw new MissingConfiguration('Login URL is not configured');
        }

        $this->logger = $logger;
        if ($logger !== null) {
            $this->debug('Loading ' . $logger::class . ' logger');
        }

        $this->client = $client ?? ClientBuilder::build();
        $this->cookie_jar = $cookieJar ?? new CookieJar();

        // start up the Capabilities tracker and add Login as the first one
        $this->capabilities = new Capabilities();
        $this->capabilities->add('Login', $loginUrl);
    }

    /**
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     * @throws \PHRETS\Exceptions\MissingConfiguration
     */
    public function Login(): Bulletin
    {
        if (!$this->configuration->valid()) {
            throw new MissingConfiguration('Cannot issue Login without a valid configuration loaded');
        }

        $response = $this->request('Login');

        /** @var \PHRETS\Parsers\Login\OneX $parser */
        $parser = $this->grab(ParserType::LOGIN);
        $xml = new \SimpleXMLElement((string) $response->getBody());
        $parser->parse((string)$xml->{'RETS-RESPONSE'});

        foreach ($parser->getCapabilities() as $k => $v) {
            $this->capabilities->add($k, $v);
        }

        $bulletin = new Bulletin($parser->getDetails());
        if ($this->capabilities->get('Action')) {
            $response = $this->request('Action');
            $bulletin->setBody((string)$response->getBody());

            return $bulletin;
        } else {
            return $bulletin;
        }
    }

    /**
     * @param string $resource
     * @param string $type
     * @param string $content_id
     * @param int $location
     *
     */
    public function GetPreferredObject(
        string $resource,
        string $type,
        string $content_id,
        int $location = 0
    ): ?BaseObject {
        $collection = $this->GetObject($resource, $type, $content_id, '0', $location);

        return $collection[0] ?? null;
    }

    /**
     * @param string $resource
     * @param string $type
     * @param string $content_ids
     * @param string $object_ids
     * @param int $location
     *
     * @return list<\PHRETS\Models\BaseObject>
     *
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     */
    public function GetObject(
        string $resource,
        string $type,
        string $content_ids,
        string|int $object_ids = '*',
        int $location = 0
    ): array {
        $request_id = GetObject::ids($content_ids, $object_ids);

        $response = $this->request(
            'GetObject',
            [
                'query' => [
                    'Resource' => $resource,
                    'Type' => $type,
                    'ID' => implode(',', $request_id),
                    'Location' => $location,
                ],
            ]
        );

        $contentType = $response->getHeader('Content-Type');

        if ($contentType !== null && stripos($contentType, 'multipart') !== false) {
            /** @var \PHRETS\Parsers\GetObject\Multiple $parser */
            $parser = $this->grab(ParserType::OBJECT_MULTIPLE);
            $collection = $parser->parse($response);
        } else {
            /** @var \PHRETS\Parsers\GetObject\Single $parser */
            $parser = $this->grab(ParserType::OBJECT_SINGLE);
            $object = $parser->parse($response);
            $collection = [ $object ];
        }

        return $collection;
    }

    /**
     *
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     */
    public function GetSystemMetadata(): \PHRETS\Models\Metadata\System
    {
        $response = $this->MakeMetadataRequest('METADATA-SYSTEM', 0);

        /** @var \PHRETS\Parsers\GetMetadata\System $parser */
        $parser = $this->grab(ParserType::METADATA_SYSTEM);
        return $parser->parse($this, $response);
    }

    /**
     * @return array<string,\PHRETS\Models\Metadata\Resource>
     *
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     */
    public function GetResourcesMetadata(): array
    {
        $response = $this->MakeMetadataRequest('METADATA-RESOURCE', 0);

        /** @var \PHRETS\Parsers\GetMetadata\Resource $parser */
        $parser = $this->grab(ParserType::METADATA_RESOURCE);

        return $parser->parse($this, $response);
    }

    /**
     * @param string $resource_id
     * @return array<string,\PHRETS\Models\Metadata\ResourceClass>
     *
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     */
    public function GetClassesMetadata(string $resource_id): array
    {
        $response = $this->MakeMetadataRequest('METADATA-CLASS', $resource_id);

        /** @var \PHRETS\Parsers\GetMetadata\ResourceClass */
        $parser = $this->grab(ParserType::METADATA_CLASS);

        return $parser->parse($this, $response);
    }

    /**
     * @param string $resource_id
     * @param string $class_id
     * @param string $keyed_by
     * @return array<string,\PHRETS\Models\Metadata\Table>
     *
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     */
    public function GetTableMetadata(string $resource_id, string $class_id, string $keyed_by = 'SystemName'): array
    {
        $response = $this->MakeMetadataRequest('METADATA-TABLE', $resource_id . ':' . $class_id);

        /** @var \PHRETS\Parsers\GetMetadata\Table $parser */
        $parser = $this->grab(ParserType::METADATA_TABLE);

        return $parser->parse($this, $response, $keyed_by);
    }

    /**
     * @param string|int $resource_id
     * @return array<string,\PHRETS\Models\Metadata\BaseObject>
     *
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     */
    public function GetObjectMetadata(string|int $resource_id): array
    {
        $response = $this->MakeMetadataRequest('METADATA-OBJECT', $resource_id);

        /** @var \PHRETS\Parsers\GetMetadata\BaseObject $parser */
        $parser = $this->grab(ParserType::METADATA_OBJECT);

        return $parser->parse($this, $response);
    }

    /**
     * @param string|int $resource_id
     * @return array<string,\PHRETS\Models\Metadata\Lookup>
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     */
    public function GetLookups(string|int $resource_id): array
    {
        $response = $this->MakeMetadataRequest('METADATA-LOOKUP', $resource_id);

        /** @var \PHRETS\Parsers\GetMetadata\Lookup $parser */
        $parser = $this->grab(ParserType::METADATA_LOOKUP);

        return $parser->parse($this, $response);
    }

    /**
     * @param string $resource_id
     * @param string $lookup_name
     * @return list<\PHRETS\Models\Metadata\LookupType>
     *
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     */
    public function GetLookupValues(string $resource_id, string $lookup_name): array
    {
        $response = $this->MakeMetadataRequest('METADATA-LOOKUP_TYPE', $resource_id . ':' . $lookup_name);

        /** @var \PHRETS\Parsers\GetMetadata\LookupType $parser */
        $parser = $this->grab(ParserType::METADATA_LOOKUPTYPE);

        return $parser->parse($this, $response);
    }

    /**
     * @param string $type
     * @param string|int $id
     *
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     */
    protected function MakeMetadataRequest(string $type, string|int $id): Response
    {
        return $this->request(
            'GetMetadata',
            [
                'query' => [
                    'Type' => $type,
                    'ID' => $id,
                    'Format' => 'STANDARD-XML',
                ],
            ]
        );
    }

    /**
     * @param string $resource_id
     * @param string $class_id
     * @param ?string $dmql_query
     * @param array{Class?:string,SearchType?:string,Query?:?string,RestrictedIndicator?:?string} $optional_parameters
     *
     *
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     */
    public function Search(
        string $resource_id,
        string $class_id,
        ?string $dmql_query,
        array $optional_parameters = [],
        bool $recursive = false
    ): Results {
        $dmql_query = Search::dmql($dmql_query);

        $defaults = [
            'SearchType' => $resource_id,
            'Class' => $class_id,
            'Query' => $dmql_query,
            'QueryType' => 'DMQL2',
            'Count' => 1,
            'Format' => 'COMPACT-DECODED',
            'Limit' => 99_999_999,
            'StandardNames' => 0,
        ];

        $parameters = array_merge($defaults, $optional_parameters);

        // if the Select parameter given is an array, format it as it needs to be
        if (array_key_exists('Select', $parameters) && is_array($parameters['Select'])) {
            $parameters['Select'] = implode(',', $parameters['Select']);
        }

        $response = $this->request(
            'Search',
            [
                'query' => $parameters,
            ]
        );

        if ($recursive) {
            /** @var \PHRETS\Parsers\Search\RecursiveOneX $parser */
            $parser = $this->grab(ParserType::SEARCH_RECURSIVE);
        } else {
            /** @var \PHRETS\Parsers\Search\OneX $parser */
            $parser = $this->grab(ParserType::SEARCH);
        }

        return $parser->parse($this, $response, $parameters);
    }

    /**
     * @param string $resource_id
     * @param string $class_id
     * @param string $action
     * @param string $data
     * @param string|null $warning_response
     * @param int $validation_mode
     * @param string $delimiter
     * @param array<string,mixed> $additional_parameters
     * @return array<string,mixed>
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     * @throws \PHRETS\Exceptions\RETSException
     */
    public function Update(
        string $resource_id,
        string $class_id,
        string $action,
        string $data,
        ?string $warning_response = null,
        int $validation_mode = 0,
        string $delimiter = '09',
        array $additional_parameters = []
    ): array {
        $parameters = [
            'Resource' => $resource_id,
            'ClassName' => $class_id,
            'Action' => $action,
            'Validate' => $validation_mode,
            'Delimiter' => $delimiter,
            'Record' => $data,
        ];

        if ($warning_response) {
            $parameters['WarningResponse'] = $warning_response;
        }

        $response = $this->request('Update', [
            'form_params' => array_merge($additional_parameters, $parameters),
        ]);

        /** @var \PHRETS\Parsers\Update\OneEight $parser */
        $parser = $this->grab(ParserType::UPDATE);

        return $parser->parse($this, $response);
    }

    /**
     * @param string $resource
     * @param string $type
     * @param string $content_type
     * @param string $action
     * @param string|resource|\Psr\Http\Message\StreamInterface $body
     * @param array<string,mixed> $attributes
     * @return array<string,mixed>
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     * @throws \PHRETS\Exceptions\RETSException
     */
    public function PostObject(
        string $resource,
        string $type,
        string $content_type,
        string $action,
        mixed $body,
        array $attributes = []
    ): array {
        $headers = array_merge([
            'Resource' => $resource,
            'Type' => $type,
            'Content-Type' => $content_type,
            'UpdateAction' => $action,
        ], $attributes);

        $response = $this->request('PostObject', [
            'headers' => $headers,
            'body' => $body,
        ]);

        /** @var \PHRETS\Parsers\PostObject\OneEight */
        $parser = $this->grab(ParserType::OBJECT_POST);

        return $parser->parse($this, $response);
    }

    /**
     *
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     */
    public function Logout(): bool
    {
        $this->request('Logout');

        return true;
    }

    /**
     *
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     */
    public function Disconnect(): bool
    {
        return $this->Logout();
    }

    /**
     * @param string $capability
     * @param array<string,mixed> $options
     * @param bool $is_retry
     *
     *
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     * @throws \PHRETS\Exceptions\RETSException
     */
    protected function request(string $capability, array $options = [], bool $is_retry = false): Response
    {
        $response = null;
        $url = $this->capabilities->get($capability);

        if (!is_string($url)) {
            throw new CapabilityUnavailable(
                "'{$capability}' tried but no valid endpoint was found.  Did you forget to Login()?"
            );
        }

        $options = array_merge($this->getDefaultOptions(), $options);

        // user-agent authentication
        if ($this->configuration->getUserAgentPassword()) {
            $headers = $options['headers'] ?? [];
            assert(is_array($headers));
            $ua_digest = $this->configuration->userAgentDigestHash($this);

            $options['headers'] = array_merge($headers, ['RETS-UA-Authorization' => 'Digest ' . $ua_digest]);
        }

        $this->debug("Sending HTTP Request for {$url} ({$capability})", $options);
        $this->last_request_url = $url;

        try {
            if (strtolower($capability) === 'postobject') {
                $this->debug('Using POST method per body option');

                $response = $this->client->request('POST', $url, [
                    'headers' => $options['headers'],
                    'body' => $options['body'],
                ]);
            } elseif ($this->configuration->readOption('use_post_method') ||
                array_key_exists('form_params', $options)
            ) {
                if (array_key_exists('form_params', $options)) {
                    $this->debug('Using POST method per form_params option');
                    $query = $options['form_params'];
                } else {
                    $this->debug('Using POST method per use_post_method option');
                    $query = (array_key_exists('query', $options)) ? $options['query'] : null;
                }

                // do not send query options in url, only in form_params
                $local_options = $options;
                unset($local_options['query']);
                $response = $this->client->request(
                    'POST',
                    $url,
                    array_merge($local_options, ['form_params' => $query])
                );
            } else {
                if (isset($options['query'])) {
                    assert(is_array($options['query']));
                    $this->last_request_url = $url . '?' . \http_build_query($options['query']);
                }

                $response = $this->client->request('GET', $url, $options);
            }
        } catch (ClientException $e) {
            $this->debug('ClientException: ' . $e->getCode() . ': ' . $e->getMessage());

            if ($e->getCode() != 401) {
                // not an Unauthorized error, so bail
                throw $e;
            }

            if ($capability == 'Login') {
                // unauthorized on a Login request, so bail
                throw $e;
            }

            if ($is_retry) {
                // this attempt was already a retry, so let's stop here
                $this->debug("Request retry failed.  Won't retry again");
                throw $e;
            }

            if ($this->getConfiguration()->readOption('disable_auto_retry')) {
                // this attempt was already a retry, so let's stop here
                $this->debug("Re-logging in disabled.  Won't retry");
                throw $e;
            }

            $this->debug('401 Unauthorized exception returned');
            $this->debug('Logging in again and retrying request');
            // see if logging in again and retrying the request works
            $this->Login();

            return $this->request($capability, $options, true);
        }

        $response = new \PHRETS\Http\Response($response);

        $this->last_response = $response;

        $cookie = $response->getHeader('Set-Cookie');
        if ($cookie !== null && preg_match('/RETS-Session-ID\=(.*?)(\;|\s+|$)/', $cookie, $matches)) {
            $this->rets_session_id = $matches[1];
        }

        $this->debug('Response: HTTP ' . $response->getStatusCode());

        if (stripos((string) $response->getHeader('Content-Type'), 'text/xml') !== false
            && $capability != 'GetObject'
        ) {
            /** @var \PHRETS\Parsers\XML $parser */
            $parser = $this->grab(ParserType::XML);
            $xml = $parser->parse($response);

            if ($xml && isset($xml['ReplyCode'])) {
                $rc = (string) $xml['ReplyCode'];

                if ($rc == '20037' && $capability != 'Login') {
                    // must make Login request again.  let's handle this automatically

                    if ($this->getConfiguration()->readOption('disable_auto_retry')) {
                        // this attempt was already a retry, so let's stop here
                        $this->debug("Re-logging in disabled.  Won't retry");
                        throw new RETSException((string)$xml['ReplyText'], (int)$xml['ReplyCode']);
                    }

                    if ($is_retry) {
                        // this attempt was already a retry, so let's stop here
                        $this->debug("Request retry failed.  Won't retry again");
                    // let this error fall through to the more generic handling below
                    } else {
                        $this->debug('RETS 20037 re-auth requested');
                        $this->debug('Logging in again and retrying request');
                        // see if logging in again and retrying the request works
                        $this->Login();

                        return $this->request($capability, $options, true);
                    }
                }

                // Return validation errors for parsing Update requests.
                if (in_array(strtolower($capability), ['update', 'postobject'])) {
                    return $response;
                }

                // 20201 - No records found - not exception worthy in my mind
                // 20403 - No objects found - not exception worthy in my mind
                if (!in_array($rc, [0, 20201, 20403])) {
                    throw new RETSException((string)$xml['ReplyText'], (int)$xml['ReplyCode']);
                }
            }
        }

        if ($this->getConfiguration()->readOption('getobject_auto_retry') && $capability == 'GetObject') {
            if (stripos((string) $response->getHeader('Content-Type'), 'multipart') !== false) {
                /** @var \PHRETS\Parsers\GetObject\Multiple $parser */
                $parser = $this->grab(ParserType::OBJECT_MULTIPLE);
                $collection = $parser->parse($response);
            } else {
                /** @var \PHRETS\Parsers\GetObject\Single $parser */
                $parser = $this->grab(ParserType::OBJECT_SINGLE);
                $object = $parser->parse($response);
                $collection = [ $object ];
            }

            foreach ($collection as $object) {
                if ($object->isError() && $object->getError()?->getCode() === '20037') {
                    if ($is_retry) {
                        // this attempt was already a retry, so let's stop here
                        $this->debug("Request retry failed.  Won't retry again");
                    // let this error fall through to the more generic handling below
                    } else {
                        $this->debug('RETS 20037 re-auth requested');
                        $this->debug('Logging in again and retrying request');
                        // see if logging in again and retrying the request works
                        $this->Login();

                        return $this->request($capability, $options, true);
                    }
                }
            }
        }

        return $response;
    }

    public function getLoginUrl(): ?string
    {
        $url = $this->capabilities->get('Login');

        return is_string($url) ? $url : null;
    }

    public function getCapabilities(): Capabilities
    {
        return $this->capabilities;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * @param array<int|string,mixed> $context
     */
    public function debug(string|Stringable $message, array|string $context = []): void
    {
        if ($this->logger) {
            if (!is_array($context)) {
                $context = [$context];
            }
            $this->logger->debug($message, $context);
        }
    }

    public function getCookieJar(): CookieJarInterface
    {
        return $this->cookie_jar;
    }

    public function getLastRequestURL(): ?string
    {
        return $this->last_request_url;
    }

    public function getLastResponse(): string
    {
        return (string) $this->last_response?->getBody();
    }

    public function getRetsSessionId(): ?string
    {
        return $this->rets_session_id;
    }

    /**
     * Note: Make sure to add a type hint to the result
     * whenever you are using this function to make sure we
     * have visibility on the parser.
     *
     * @param \PHRETS\Parsers\ParserType $parser
     */
    protected function grab(ParserType $parser): mixed
    {
        return $this->configuration->getStrategy()->provide($parser);
    }

    /**
     * @return array{
     *   auth:list<?string>,
     *   headers: array{User-Agent: string, RETS-Version: string, Accept-Encoding: string, Accept: string},
     *   curl: array<int, string>,
     *   allow_redirects?: false
     * }
     */
    public function getDefaultOptions(): array
    {
        $defaults = [
            'auth' => [
                $this->configuration->getUsername(),
                $this->configuration->getPassword(),
                $this->configuration->getHttpAuthenticationMethod(),
            ],
            'headers' => [
                'User-Agent' => $this->configuration->getUserAgent(),
                'RETS-Version' => $this->configuration->getRetsVersion()->asHeader(),
                'Accept-Encoding' => 'gzip',
                'Accept' => '*/*',
            ],
            'curl' => [CURLOPT_COOKIEFILE => ''],
        ];

        // disable following 'Location' header (redirects) automatically
        if ($this->configuration->readOption('disable_follow_location')) {
            $defaults['allow_redirects'] = false;
        }

        return $defaults;
    }
}
