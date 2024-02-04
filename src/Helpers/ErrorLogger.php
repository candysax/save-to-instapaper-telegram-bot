<?php

namespace SaveToInstapaperBot\Helpers;

class ErrorLogger
{
    public static function print(string $actionName, string $prodMessage, $e)
    {
        return $_ENV['MODE'] == 'production' ? $prodMessage : $actionName . ' ' . $e->getMessage();
    }
}
