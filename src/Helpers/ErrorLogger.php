<?php

namespace SaveToInstapaperBot\Helpers;

use SaveToInstapaperBot\Base\Bot;

class ErrorLogger
{
    public static function print(string $alias, string $prodMessage, $exception): string
    {
        return $_ENV['MODE'] == 'production' ? $prodMessage : $alias . ' ' . $exception->getMessage();
    }

    public static function sendDefaultError(string $alias, string $chatId, $exception): void
    {
        Bot::api()->sendMessage([
            'chat_id' => $chatId,
            'text' => static::print(
                $alias,
                '❗ Sorry, something went wrong. Please try again later.',
                $exception
            ),
        ]);
    }
}
