<?php

namespace SaveToInstapaperBot\Commands;

use SaveToInstapaperBot\Base\Database;
use SaveToInstapaperBot\Enums\CommandName;
use SaveToInstapaperBot\Enums\AuthStage;
use SaveToInstapaperBot\Processors\AuthProcessor;
use SaveToInstapaperBot\Services\Auth;
use Telegram\Bot\Commands\Command;

class LogoutCommand extends Command
{
    protected string $name = CommandName::LOGOUT->value;
    protected string $description = 'log out of your Instapaper account.';

    public function handle(): void
    {
        $message = $this->getUpdate()->getMessage();
        $chatId = $message->getChat()->getId();

        if (Auth::isLogged($chatId)) {
            Auth::logout($chatId);
            $this->replyWithMessage([
                'text' => 'You have logged out of your Instapaper account.',
            ]);

            Database::set('auth_stage', AuthStage::AUTHORIZING_STARTED->value, $chatId);

            AuthProcessor::rerun($message);
        } else {
            $this->replyWithMessage([
                'text' => 'Before you log out of your account, you need to log in to it.',
            ]);
        }
    }
}
