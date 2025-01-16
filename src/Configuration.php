<?php

namespace PHRETS;

use PHRETS\Enums\RETSVersion;
use PHRETS\Exceptions\InvalidConfiguration;
use PHRETS\Strategies\SimpleStrategy;
use PHRETS\Strategies\Strategy;

class Configuration
{
    public const AUTH_BASIC = 'basic';
    public const AUTH_DIGEST = 'digest';

    protected ?string $username = null;
    protected ?string $password = null;
    protected ?string $login_url = null;
    protected string $user_agent = 'PHRETS/2.6.4';
    protected ?string $user_agent_password = null;
    protected readonly RETSVersion $rets_version;
    protected readonly Strategy $strategy;
    protected string $http_authentication = 'digest';

    /** @var array<string,mixed> */
    protected array $options = [];

    public function __construct(
        ?Strategy $strategy = null,
        ?RETSVersion $version = null
    ) {
        $this->rets_version = $version ?? RETSVersion::VERSION_1_5;
        $this->strategy = $strategy ?? new SimpleStrategy();

        $this->strategy->initialize($this);
    }

    public function getLoginUrl(): ?string
    {
        return $this->login_url;
    }

    /**
     */
    public function setLoginUrl(?string $login_url): self
    {
        $this->login_url = $login_url;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRetsVersion(): RETSVersion
    {
        return $this->rets_version;
    }

    public function getUserAgent(): string
    {
        return $this->user_agent;
    }

    public function setUserAgent(string $user_agent): self
    {
        $this->user_agent = $user_agent;

        return $this;
    }

    public function getUserAgentPassword(): ?string
    {
        return $this->user_agent_password;
    }

    public function setUserAgentPassword(?string $user_agent_password): self
    {
        $this->user_agent_password = $user_agent_password;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function setOption(string $name, mixed $value): self
    {
        $this->options[$name] = $value;

        return $this;
    }

    public function readOption(string $name): mixed
    {
        return $this->options[$name] ?? null;
    }

    /**
     * @param array<string,string> $configuration
     *
     *
     * @throws \PHRETS\Exceptions\InvalidConfiguration
     */
    public static function load(array $configuration = []): self
    {
        $variables = [
            'username' => 'Username',
            'password' => 'Password',
            'login_url' => 'LoginUrl',
            'user_agent' => 'UserAgent',
            'user_agent_password' => 'UserAgentPassword',
            'http_authentication' => 'HttpAuthenticationMethod',
        ];

        $version = null;
        $retsVersion = $configuration['rets_version'] ?? null;
        if ($retsVersion !== null && $retsVersion !== '') {
            if (str_starts_with($retsVersion, 'RETS/')) {
                $retsVersion = substr($retsVersion, strlen('RETS/'));
            }
            $version = RETSVersion::tryFrom($retsVersion);
            if ($version === null) {
                throw new InvalidConfiguration('Invalid RETS version: ' . $retsVersion);
            }
        }

        $me = new self(version: $version);

        foreach ($variables as $k => $m) {
            if (array_key_exists($k, $configuration)) {
                $method = 'set' . $m;
                $me->$method($configuration[$k]);
            }
        }

        if (!$me->valid()) {
            throw new InvalidConfiguration('Login URL and Username must be provided');
        }

        return $me;
    }

    /**
     */
    public function valid(): bool
    {
        return $this->getLoginUrl() && $this->getUsername();
    }

    /**
     */
    public function getStrategy(): Strategy
    {
        return $this->strategy;
    }

    /**
     */
    public function userAgentDigestHash(Session $session): string
    {
        $ua_a1 = md5($this->getUserAgent() . ':' . $this->getUserAgentPassword());

        return md5(
            trim($ua_a1) . '::' . trim((string) $session->getRetsSessionId()) .
            ':' . trim($this->getRetsVersion()->asHeader())
        );
    }

    public function setHttpAuthenticationMethod(string $auth_method): self
    {
        if (!in_array($auth_method, [self::AUTH_BASIC, self::AUTH_DIGEST])) {
            throw new \InvalidArgumentException("Given authentication method is invalid.  Must be 'basic' or 'digest'");
        }
        $this->http_authentication = $auth_method;

        return $this;
    }

    public function getHttpAuthenticationMethod(): string
    {
        return $this->http_authentication;
    }
}
