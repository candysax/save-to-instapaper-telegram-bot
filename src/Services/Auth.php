<?php

namespace SaveToInstapaperBot\Services;

use Psr\Http\Message\ResponseInterface;
use SaveToInstapaperBot\Adapters\InstapaperAdapter;
use SaveToInstapaperBot\Base\Bot;
use SaveToInstapaperBot\Base\Database;
use SaveToInstapaperBot\Helpers\AuthStage;
use PHPOnCouch\Exceptions\CouchNotFoundException;
use SaveToInstapaperBot\Helpers\ErrorLogger;

class Auth
{
    public static function isLogged(string $chatId): bool
    {
        if (intval(Database::get('auth_stage', $chatId)) === AuthStage::AUTHORIZED) {
            return true;
        }

        return false;
    }

    public static function login(string $username, string $password): ResponseInterface
    {
        return InstapaperAdapter::auth($username, $password);
    }

    public static function logout(string $chatId): bool
    {
        return Database::delete($chatId);
    }
}
