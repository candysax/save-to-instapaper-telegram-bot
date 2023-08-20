<?php

namespace SaveToInstapaperBot\Commands;

use SaveToInstapaperBot\Base\Database;
use SaveToInstapaperBot\Helpers\CommandName;
use SaveToInstapaperBot\Helpers\AuthStage;
use SaveToInstapaperBot\Processors\AuthProcessor;
use SaveToInstapaperBot\Services\Auth;
use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{
    protected string $name = CommandName::START;
    protected string $description = 'Launching SaveToInstapaperBot';

    public function handle()
    {
        $message = $this->getUpdate()->getMessage();
        $chatId = $message->getChat()->getId();

        if (!Auth::isLogged($chatId)) {
            $this->replyWithMessage([
                'text' => "Hi! This is a bot for saving links and articles to Instapaper from Telegram.\n" .
                "Please, log in to your Instapaper account",
            ]);

            Database::set('auth_stage', AuthStage::AUTHORIZING_STARTED, $chatId);
            AuthProcessor::processMessage($message);
        }
    }
}
