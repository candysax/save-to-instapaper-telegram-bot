<?php

namespace SaveToInstapaperBot\Base;

use Telegram\Bot\Api;

class Bot
{
    private static $bot;

    public static function getInstance()
    {
        if (static::$bot) {
            return static::$bot;
        }
        static::$bot = new Api($_ENV['BOT_TOKEN']);;

        return static::$bot;
    }
}
