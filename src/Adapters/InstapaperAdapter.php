<?php

namespace SaveToInstapaperBot\Adapters;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class InstapaperAdapter
{
    public static function auth(string $username, string $password): ResponseInterface
    {
        $client = new Client();

        return $client->request('POST', $_ENV['AUTH_IP_URL'], [
            'form_params' => [
                'username' => $username,
                'password' => $password,
            ],
        ]);
    }


    public static function save(string $url, array $credentials): ResponseInterface
    {
        $client = new Client();

        return $client->request('POST', $_ENV['SAVE_IP_URL'], [
            'form_params' => [
                'username' => $credentials['username'],
                'password' => $credentials['password'],
                'url' => $url,
            ],
        ]);
    }
}
