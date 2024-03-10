<?php

namespace SaveToInstapaperBot\Commands;

use SaveToInstapaperBot\Helpers\CommandName;
use Telegram\Bot\Commands\Command;

class HelpCommand extends Command
{
    protected string $name = CommandName::HELP;
    protected string $description = 'show a list of available commands.';

    public function handle(): void
    {
        $commands = $this->telegram->getCommands();

        $text = "You can use the following commands:" . PHP_EOL;
        foreach ($commands as $name => $handler) {
            $text .= '/' . $name . ' – ' . $handler->getDescription() . PHP_EOL;
        }
        $text .= PHP_EOL . 'To save a message, just send it to the chat bot.';

        $this->replyWithMessage([
            'text' => $text,
        ]);
    }
}
