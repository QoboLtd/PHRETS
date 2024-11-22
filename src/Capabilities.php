<?php

namespace PHRETS;

class Capabilities
{
    /**
     * @var array<string,bool|int|string>
     */
    protected array $capabilities = [];

    /**
     * @param string $name
     * @param bool|string|int $capability
     */
    public function add(string $name, bool|string|int $capability): void
    {
        if (!is_string($capability)) {
            $this->capabilities[$name] = $capability;

            return;
        }

        $parts = parse_url($capability);
        if (!is_array($parts) || array_key_exists('host', $parts)) {
            $this->capabilities[$name] = $capability;

            return;
        }

        // relative URL given, so build this into an absolute URL
        $login_url = $this->get('Login');
        if (!is_string($login_url)) {
            throw new \InvalidArgumentException("Cannot automatically determine absolute path for '{$capability}' given");
        }

        $parts = parse_url($login_url);
        assert(isset($parts['scheme']));
        assert(isset($parts['host']));

        $uri = $parts['scheme'] . '://' . $parts['host'] . ':';
        $uri .= (empty($parts['port'])) ? (($parts['scheme'] === 'https') ? 443 : 80) : $parts['port'];
        $uri .= $capability;

        $this->capabilities[$name] = $uri;
    }

    public function get(string $name): bool|string|int|null
    {
        return $this->capabilities[$name] ?? null;
    }
}
