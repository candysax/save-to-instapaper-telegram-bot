<?php

namespace SaveToInstapaperBot\Services;

use SaveToInstapaperBot\Adapters\InstapaperAdapter;
use SaveToInstapaperBot\Base\Bot;
use SaveToInstapaperBot\Base\Database;
use SaveToInstapaperBot\Helpers\AuthStage;
use PHPOnCouch\Exceptions\CouchNotFoundException;

class Auth
{
    public static function isLogged(string $chatId)
    {
        $db = Database::getInstance();

        try {
            $user = $db->getDoc($chatId);
            if ($user->auth_stage !== AuthStage::AUTHORIZED) {
                return false;
            }
            return true;
        } catch (CouchNotFoundException $e) {
            return false;
        } catch (\Exception $e) {
            Bot::getInstance()->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Sorry, something went wrong. Please try again later.'
            ]);
        }
    }

    public static function login(string $username, string $password)
    {
        return InstapaperAdapter::auth($username, $password);
    }

    public static function logout(string $chatId)
    {
        $db = Database::getInstance();

        try {
            $user = $db->getDoc($chatId);
            $db->deleteDoc($user);
        } catch (\Exception $e) {
            Bot::getInstance()->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Sorry, something went wrong. Please try again later.'
            ]);
        }
    }
}
