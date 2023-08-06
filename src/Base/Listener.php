<?php

namespace Papergram\Base;

use Papergram\Base\Auth;
use Papergram\Helpers\CommandName;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Message;

class Listener
{
    public static function processMessage(Message $message, Api $bot)
    {
        $chatId = $message->getChat()->getId();
        $text = $message->getText();

        if (strpos($text, '/') !== 0) {
            if (Auth::isLogged($chatId)) {
                Saver::save($message);
            } else {
                Auth::process($message);
            }
        }
    }
}
