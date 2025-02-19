<?php
declare(strict_types=1);

namespace PHRETS\Http;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class ClientBuilder
{
    private static ?ClientInterface $instance = null;

    /**
     * Gets the default client
     */
    public static function build(): ClientInterface
    {
        if (self::$instance === null) {
            self::$instance = new Client([]);
        }

        return self::$instance;
    }

    /**
     * Sets the default client to use for sessions.
     */
    public static function set(?ClientInterface $client): void
    {
        self::$instance = $client;
    }
}
