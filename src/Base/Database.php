<?php

namespace SaveToInstapaperBot\Base;

use PHPOnCouch\CouchClient;
use PHPOnCouch\Exceptions\CouchNotFoundException;
use stdClass;

class Database
{
    private static $db;

    public static function getInstance()
    {
        if (static::$db) {
            return static::$db;
        }
        static::$db = new CouchClient("http://{$_ENV['DB_HOST']}:{$_ENV['DB_PORT']}", $_ENV['DB_NAME'], [
            'username' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],
        ]);

        return static::$db;
    }

    public static function set(string $key, int|string|bool $value, string $chatId)
    {
        $db = static::getInstance();

        try {
            $user = $db->getDoc($chatId);
            $user->{$key} = $value;

            $db->storeDoc($user);
        } catch (CouchNotFoundException $e) {
            $user = new stdClass();
            $user->_id = $chatId;
            $user->{$key} = $value;

            $db->storeDoc($user);
        } catch (\Exception $e) {
            Bot::getInstance()->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Sorry, something went wrong. Please try again later.'
            ]);
        }
    }

    public static function get(string $key, string $chatId)
    {
        $db = static::getInstance();

        try {
            $user = $db->getDoc($chatId);
            return $user->{$key};
        } catch (\Exception $e) {
            Bot::getInstance()->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Sorry, something went wrong. Please try again later.'
            ]);
        }
    }
}
