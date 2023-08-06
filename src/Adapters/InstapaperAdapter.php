<?php

namespace Papergram\Adapters;

use GuzzleHttp\Client;

class InstapaperAdapter
{
    public static function auth(string $username, string $password)
    {
        $client = new Client();

        return $client->request('POST', $_ENV['AUTH_IP_URL'], [
            'form_params' => [
                'username' => $username,
                'password' => $password,
            ],
        ]);
    }

    public static function save(string $url, array $credentials)
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