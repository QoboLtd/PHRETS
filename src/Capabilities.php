<?php

namespace PHRETS;

class Capabilities
{
    /**
     * @var array<string,string>
     */
    protected array $capabilities = [];

    /**
     * @param string $name
     * @param string $uri
     *
     * @return self
     */
    public function add(string $name, string $uri): self
    {
        $parts = [];
        $new_uri = null;
        $parse_results = parse_url($uri);
        if (!array_key_exists('host', $parse_results) || !$parse_results['host']) {
            // relative URL given, so build this into an absolute URL
            $login_url = $this->get('Login');
            if (!$login_url) {
                throw new \InvalidArgumentException("Cannot automatically determine absolute path for '{$uri}' given");
            }

            $parts = parse_url($login_url);

            $new_uri = $parts['scheme'] . '://' . $parts['host'] . ':';
            $new_uri .= (empty($parts['port'])) ? (($parts['scheme'] == 'https') ? 443 : 80) : $parts['port'];
            $new_uri .= $uri;

            $uri = $new_uri;
        }

        $this->capabilities[$name] = $uri;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return ?string
     */
    public function get(string $name): ?string
    {
        return $this->capabilities[$name] ?? null;
    }
}
