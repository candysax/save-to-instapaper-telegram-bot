<?php

namespace SaveToInstapaperBot\Commands;

use SaveToInstapaperBot\Base\Database;
use SaveToInstapaperBot\Enums\CommandName;
use SaveToInstapaperBot\Enums\AuthStage;
use SaveToInstapaperBot\Processors\AuthProcessor;
use SaveToInstapaperBot\Services\Auth;
use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{
    protected string $name = CommandName::START->value;
    protected string $description = 'launch the bot.';

    public function handle(): void
    {
        $message = $this->getUpdate()->getMessage();
        $chatId = $message->getChat()->getId();

        if (!Auth::isLogged($chatId)) {
            $this->replyWithMessage([
                'text' => "Hey! 👋\nThis bot will help you save links, messages and posts to Instapaper from Telegram.\n" .
                "Please, log in to your Instapaper account.",
            ]);

            Database::set('auth_stage', AuthStage::AUTHORIZING_STARTED->value, $chatId);

            AuthProcessor::run($message);
        } else {
            $this->replyWithMessage([
                'text' => 'You are already logged into your Instapaper account.',
            ]);
        }
    }
}
