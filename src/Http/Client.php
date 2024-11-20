<?php

namespace PHRETS\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;

class Client
{
    protected static ?GuzzleClient $client = null;

    public static function make(array $options = []): GuzzleClient
    {
        if (!self::$client) {
            self::$client = new GuzzleClient($options);
        }

        return self::$client;
    }

    public static function set(ClientInterface $client)
    {
        self::$client = $client;
    }
}
