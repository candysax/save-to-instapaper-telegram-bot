<?php

require_once __DIR__ . '/vendor/autoload.php';
// require_once __DIR__ . '/webhook.php';

use Papergram\Base\Listener;
use Papergram\Commands\HelpCommand;
use Papergram\Commands\LogoutCommand;
use Papergram\Commands\StartCommand;
use Telegram\Bot\Api;

if (file_exists('.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);;
    $dotenv->load();
}

$bot = new Api($_ENV['BOT_TOKEN']);

$bot->addCommands([
    HelpCommand::class,
    StartCommand::class,
    LogoutCommand::class,
]);
$bot->commandsHandler(true);

$update = $bot->getWebhookUpdate();

if ($update->has('message')) {
    $message = $update->getMessage();

    Listener::processMessage($message, $bot);
}
