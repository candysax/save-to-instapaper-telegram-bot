<?php

namespace SaveToInstapaperBot\Base;

use Telegram\Bot\Api;

class Bot
{
    private static $bot;

    public static function api()
    {
        if (static::$bot) {
            return static::$bot;
        }

        static::$bot = new Api($_ENV['BOT_TOKEN']);;

        return static::$bot;
    }
}
