<?php

namespace SaveToInstapaperBot\Adapters;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Psr\Http\Message\ResponseInterface;

class InstapaperAdapter
{
    public static function token(string $username, string $password): ResponseInterface
    {
        $client = new Client([
            'handler' => static::handler(),
            'auth' => 'oauth',
        ]);

        return $client->request('POST', $_ENV['ACCESS_TOKEN_IP_URL'], [
            'form_params' => [
                'x_auth_username' => $username,
                'x_auth_password' => $password,
                'x_auth_mode' => 'client_auth',
            ],
        ]);
    }

    public static function save(string $url, array $authData): ResponseInterface
    {
        $client = new Client([
            'handler' => static::handler($authData),
            'auth' => 'oauth',
        ]);

        return $client->request('POST', $_ENV['SAVE_IP_URL'], [
            'form_params' => [
                'url' => $url,
            ],
        ]);
    }

    protected static function handler(array $token = []): HandlerStack
    {
        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key'    => $_ENV['OAUTH_CONSUMER_ID'],
            'consumer_secret' => $_ENV['OAUTH_CONSUMER_SECRET'],
            'token'           => $token['token'] ?? '',
            'token_secret'    => $token['token_secret'] ?? '',
        ]);
        $stack->push($middleware);

        return $stack;
    }
}
