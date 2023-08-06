<?php

namespace Papergram\Commands;

use Papergram\Base\Auth;
use Papergram\Helpers\CommandName;
use Papergram\Helpers\Stage;
use Papergram\Helpers\Text;
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
            Auth::set('stage', Stage::AUTHORIZING_STARTED, $this->getUpdate()->getMessage()->getChat()->getId());
            Auth::process($message);
        } else {
            $this->replyWithMessage([
                'text' => 'Before you log out of your account, you need to log in to it.',
            ]);
        }
    }
}