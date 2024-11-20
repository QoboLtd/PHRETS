<?php

namespace PHRETS\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;

class Client
{
    protected static ?ClientInterface $client = null;

    public static function make(array $options = []): ClientInterface
    {
        if (self::$client === null) {
            self::$client = new GuzzleClient($options);
        }

        return self::$client;
    }

    public static function set(ClientInterface $client): void
    {
        self::$client = $client;
    }
}
