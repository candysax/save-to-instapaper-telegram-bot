<?php

namespace SaveToInstapaperBot\Handlers;

use SaveToInstapaperBot\Processors\AuthProcessor;
use SaveToInstapaperBot\Processors\SaverProcessor;
use SaveToInstapaperBot\Services\Auth;
use Telegram\Bot\Objects\CallbackQuery;

class CallbackHandler
{
    public static function handle(CallbackQuery $callbackQuery)
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();

        if (Auth::isLogged($chatId)) {
            SaverProcessor::processCallbackQuery($callbackQuery);
        } else {
            AuthProcessor::processMessage($callbackQuery->getMessage());
        }
    }
}
