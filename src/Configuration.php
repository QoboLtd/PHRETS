<?php

namespace PHRETS;

use PHRETS\Exceptions\InvalidConfiguration;
use PHRETS\Strategies\SimpleStrategy;
use PHRETS\Strategies\Strategy;
use PHRETS\Versions\RETSVersion;

class Configuration
{
    public const AUTH_BASIC = 'basic';
    public const AUTH_DIGEST = 'digest';

    protected ?string $username;
    protected ?string $password;
    protected ?string $login_url;
    protected string $user_agent = 'PHRETS/2.6.4';
    protected ?string $user_agent_password;
    protected RETSVersion $rets_version;
    protected string $http_authentication = 'digest';
    protected ?Strategy $strategy = null;

    /** @var array<string,mixed> */
    protected array $options = [];

    public function __construct()
    {
        $this->rets_version = (new RETSVersion())->setVersion('1.5');
    }

    public function getLoginUrl(): ?string
    {
        return $this->login_url;
    }

    /**
     * @return self
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

    /**
     * @param string $rets_version
     *
     * @return $this
     */
    public function setRetsVersion(string $rets_version)
    {
        $this->rets_version = (new RETSVersion())->setVersion($rets_version);

        return $this;
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
     * @return self
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
            'rets_version' => 'RetsVersion',
            'http_authentication' => 'HttpAuthenticationMethod',
        ];

        $me = new self();

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
     * @return bool
     */
    public function valid()
    {
        return $this->getLoginUrl() && $this->getUsername();
    }

    /**
     * @return \PHRETS\Strategies\Strategy
     */
    public function getStrategy()
    {
        if ($this->strategy === null) {
            $this->setStrategy(new SimpleStrategy());
        }

        return $this->strategy;
    }

    /**
     * @return $this
     */
    public function setStrategy(Strategy $strategy)
    {
        $strategy->initialize($this);
        $this->strategy = $strategy;

        return $this;
    }

    /**
     * @return string
     */
    public function userAgentDigestHash(Session $session)
    {
        $ua_a1 = md5($this->getUserAgent() . ':' . $this->getUserAgentPassword());

        return md5(
            trim((string) $ua_a1) . '::' . trim((string) $session->getRetsSessionId()) .
            ':' . trim((string) $this->getRetsVersion()->asHeader())
        );
    }

    /**
     * @param $auth_method
     *
     * @return $this
     */
    public function setHttpAuthenticationMethod(string $auth_method)
    {
        if (!in_array($auth_method, [self::AUTH_BASIC, self::AUTH_DIGEST])) {
            throw new \InvalidArgumentException("Given authentication method is invalid.  Must be 'basic' or 'digest'");
        }
        $this->http_authentication = $auth_method;

        return $this;
    }

    /**
     * @return string
     */
    public function getHttpAuthenticationMethod(): string
    {
        return $this->http_authentication;
    }
}
