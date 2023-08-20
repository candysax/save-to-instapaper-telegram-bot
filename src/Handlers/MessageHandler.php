<?php

namespace SaveToInstapaperBot\Handlers;

use SaveToInstapaperBot\Processors\AuthProcessor;
use SaveToInstapaperBot\Processors\SaverProcessor;
use SaveToInstapaperBot\Services\Auth;
use Telegram\Bot\Objects\Message;

class MessageHandler
{
    public static function handle(Message $message)
    {
        $chatId = $message->getChat()->getId();
        $text = $message->getText();

        if (strpos($text, '/') !== 0) {
            if (Auth::isLogged($chatId)) {
                SaverProcessor::processMessage($message);
            } else {
                AuthProcessor::processMessage($message);
            }
        }
    }
}
