<?php

namespace SaveToInstapaperBot\Handlers;

use SaveToInstapaperBot\Processors\AuthProcessor;
use SaveToInstapaperBot\Processors\SaverMessageProcessor;
use SaveToInstapaperBot\Services\Auth;
use Telegram\Bot\Objects\Message;

class MessageHandler extends BaseHandler
{
    public function handle(Message $message): void
    {
        $chatId = $message->getChat()->getId();
        $text = $message->getText();

        if ($this->isNotCommand($text)) {
            if (Auth::isLogged($chatId)) {
                SaverMessageProcessor::run($message);
            } else {
                AuthProcessor::run($message);
            }
        }
    }

    protected function isNotCommand($text): bool
    {
        return !str_starts_with($text, '/');
    }
}
