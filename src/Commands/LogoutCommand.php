<?php

namespace SaveToInstapaperBot\Commands;

use SaveToInstapaperBot\Base\Database;
use SaveToInstapaperBot\Helpers\CommandName;
use SaveToInstapaperBot\Helpers\AuthStage;
use SaveToInstapaperBot\Processors\AuthProcessor;
use SaveToInstapaperBot\Services\Auth;
use Telegram\Bot\Commands\Command;

class LogoutCommand extends Command
{
    protected string $name = CommandName::LOGOUT;
    protected string $description = 'Log out of your instapaper account.';

    public function handle()
    {
        $message = $this->getUpdate()->getMessage();
        $chatId = $message->getChat()->getId();

        if (Auth::isLogged($chatId)) {
            Auth::logout($chatId);
            $this->replyWithMessage([
                'text' => 'You have logged out of your Instapaper account.',
            ]);
            Database::set('auth_stage', AuthStage::AUTHORIZING_STARTED, $chatId);
            AuthProcessor::processMessage($message);
        } else {
            $this->replyWithMessage([
                'text' => 'Before you log out of your account, you need to log in to it.',
            ]);
        }
    }
}
