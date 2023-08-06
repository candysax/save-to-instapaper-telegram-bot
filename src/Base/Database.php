<?php

namespace Papergram\Base;

use PHPOnCouch\CouchClient;

class Database
{
    private static $db;

    public static function getInstance()
    {
        if (static::$db) {
            return static::$db;
        }
        static::$db = new CouchClient("http://{$_ENV['DB_HOST']}:{$_ENV['DB_PORT']}", 'papergramdb', [
            'username' => 'quadice',
            'password' => 'Gadore88+',
        ]);

        return static::$db;
    }
}
