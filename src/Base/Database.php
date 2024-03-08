<?php

namespace SaveToInstapaperBot\Base;

use PHPOnCouch\CouchClient;
use PHPOnCouch\Exceptions\CouchNotFoundException;
use SaveToInstapaperBot\Helpers\ErrorLogger;
use stdClass;

class Database
{
    private static $db;

    public static function api()
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

    public static function set(string $key, int|string|bool $value, string $chatId): void
    {
        $db = static::api();

        try {
            $user = $db->getDoc($chatId);
            $user->{$key} = $value;

            $db->storeDoc($user);
        } catch (CouchNotFoundException $e) {
            $user = new stdClass();

            $user->_id = $chatId;
            $user->{$key} = $value;

            $db->storeDoc($user);
        }
        catch (\Exception $e) {
            ErrorLogger::sendDefaultError('set', $e, $chatId);
        }
    }

    public static function get(string $key, string $chatId): string
    {
        $db = static::api();

        try {
            $user = $db->getDoc($chatId);

            return $user->{$key};
        } catch (\Exception $e) {
            ErrorLogger::sendDefaultError('get', $e, $chatId);

            return '';
        }
    }

    public static function delete(string $chatId): bool
    {
        $db = static::api();

        try {
            $user = $db->getDoc($chatId);
            $db->deleteDoc($user);

            return true;
        } catch (\Exception $e) {
            ErrorLogger::sendDefaultError('delete', $e, $chatId);

            return false;
        }
    }
}
