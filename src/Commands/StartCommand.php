<?php

namespace Papergram\Commands;

use Papergram\Base\Auth;
use Papergram\Helpers\CommandName;
use Papergram\Helpers\Stage;
use Papergram\Helpers\Text;
use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{
    protected string $name = CommandName::START;
    protected string $description = 'Launching Papergram';

    public function handle()
    {
        $message = $this->getUpdate()->getMessage();
        $chatId = $message->getChat()->getId();

        if (!Auth::isLogged($chatId)) {
            $this->replyWithMessage([
                'text' => Text::startMassage(),
            ]);

            Auth::set('stage', Stage::AUTHORIZING_STARTED, $chatId);
            Auth::process($message);
        }
    }
}