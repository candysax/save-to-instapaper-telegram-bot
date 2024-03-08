<?php

namespace SaveToInstapaperBot\Handlers;

use SaveToInstapaperBot\Processors\AuthProcessor;
use SaveToInstapaperBot\Processors\SaverCallbackQueryProcessor;
use SaveToInstapaperBot\Services\Auth;
use Telegram\Bot\Objects\CallbackQuery;

class CallbackHandler extends BaseHandler
{
    public function handle(CallbackQuery $callbackQuery): void
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();

        if (Auth::isLogged($chatId)) {
            SaverCallbackQueryProcessor::catch($callbackQuery);
        } else {
            AuthProcessor::run($callbackQuery->getMessage());
        }
    }
}
