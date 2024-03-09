<?php

namespace SaveToInstapaperBot\Services;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use SaveToInstapaperBot\Adapters\InstapaperAdapter;
use SaveToInstapaperBot\Base\Database;
use SaveToInstapaperBot\Helpers\AuthStage;

class Auth
{
    protected const SUCCESSFUL = 200;

    public static function isLogged(string $chatId): bool
    {
        if (intval(Database::get('auth_stage', $chatId)) === AuthStage::AUTHORIZED) {
            return true;
        }

        return false;
    }

    public static function logout(string $chatId): bool
    {
        return Database::delete($chatId);
    }

    public static function getToken(string $username, string $password, array &$authData): bool
    {
        $response = InstapaperAdapter::token($username, $password);

        if ($response->getStatusCode() === static::SUCCESSFUL) {
            parse_str($response->getBody()->getContents(), $authData);

            return true;
        }

        return false;
    }
}
