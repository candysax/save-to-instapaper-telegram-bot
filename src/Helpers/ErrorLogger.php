<?php

namespace SaveToInstapaperBot\Helpers;

use SaveToInstapaperBot\Base\Bot;

class ErrorLogger
{
    public static function print(string $actionName, string $prodMessage, $e): string
    {
        return $_ENV['MODE'] == 'production' ? $prodMessage : $actionName . ' ' . $e->getMessage();
    }

    public static function sendDefaultError(string $actionName, $exception, string $chatId): void
    {
        Bot::api()->sendMessage([
            'chat_id' => $chatId,
            'text' => static::print(
                $actionName,
                '❗ Sorry, something went wrong. Please try again later.',
                $exception
            ),
        ]);
    }
}
