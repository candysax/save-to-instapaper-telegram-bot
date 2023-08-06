<?php

namespace Papergram\Helpers;

class Text
{
    public static function startMassage(): string {
        return "Hi! This is a bot for saving links and articles to Instapaper from Telegram.\n" .
        "Please, log in to your Instapaper account";
    }

    public static function enterUsernameMessage(): string {
        return "Enter your email:";
    }

    public static function enterPasswordMessage(): string {
        return "Enter your password:";
    }
}