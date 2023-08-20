<?php

namespace SaveToInstapaperBot\Helpers;

class Text
{
    const TEXT = [
        'start' => [
            'en' => "Hi! This is a bot for saving links and articles to Instapaper from Telegram.\n" .
            "Please, log in to your Instapaper account",
        ],
        'enter_username' => [

        ],
    ];

    public static function startMassage() {
        return "Hi! This is a bot for saving links and articles to Instapaper from Telegram.\n" .
        "Please, log in to your Instapaper account";
        return [
            'en' => 'Enter your email:'
        ];
    }

    public static function enterUsernameMessage() {
        return [
            'en' => 'Enter your email:'
        ];
    }

    public static function enterPasswordMessage() {
        return [
            'en' => 'Enter your password:'
        ];
    }
}
