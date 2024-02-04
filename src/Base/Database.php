<?php

namespace SaveToInstapaperBot\Base;

use PHPOnCouch\CouchClient;
use PHPOnCouch\Exceptions\CouchNotFoundException;
use SaveToInstapaperBot\Helpers\ErrorLogger;
use stdClass;

class Database
{
    private static $db;

    public static function getInstance()
    {
        if (static::$db) {
            return static::$db;
        }
        $client = new CouchClient(
            $_ENV['DB_HOST_PROTOCOL'] . '://' . $_ENV['DB_HOST'] . ':' . $_ENV['DB_PORT'],
            $_ENV['DB_NAME'],
            [
                'username' => $_ENV['DB_USERNAME'],
                'password' => $_ENV['DB_PASSWORD'],
            ]
        );

        try {
            $client->createDatabase();
            static::$db = $client;
        } catch (\Exception $e) {
            static::$db = $client;
        }

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
                'text' => ErrorLogger::print(
                    'set',
                    '❗ Sorry, something went wrong. Please try again later.',
                    $e
                ),
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
                'text' => ErrorLogger::print(
                    'get',
                    '❗ Sorry, something went wrong. Please try again later.',
                    $e
                ),
            ]);
        }
    }
}
