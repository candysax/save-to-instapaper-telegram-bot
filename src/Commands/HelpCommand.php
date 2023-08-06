<?php

namespace Papergram\Commands;

use Papergram\Helpers\CommandName;
use Papergram\Helpers\Text;
use Telegram\Bot\Commands\Command;

class HelpCommand extends Command
{
    protected string $name = CommandName::HELP;
    protected string $description = 'Navigates the available commands';

    public function handle()
    {
        $this->replyWithMessage([
            'text' => 'help command',
        ]);
    }
}