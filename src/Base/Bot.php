<?php

namespace SaveToInstapaperBot\Base;

use Telegram\Bot\Api;

class Bot
{
    protected static Api $bot;

    public static function api()
    {
        if (!empty(static::$bot)) {
            return static::$bot;
        }

        static::$bot = new Api($_ENV['BOT_TOKEN']);;

        return static::$bot;
    }
}
