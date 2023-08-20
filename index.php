<?php

require_once __DIR__ . '/vendor/autoload.php';

use SaveToInstapaperBot\Commands\HelpCommand;
use SaveToInstapaperBot\Commands\LogoutCommand;
use SaveToInstapaperBot\Commands\StartCommand;
use SaveToInstapaperBot\Handlers\CallbackHandler;
use SaveToInstapaperBot\Handlers\MessageHandler;
use Telegram\Bot\Api;

if (file_exists('.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);;
    $dotenv->load();
}

$bot = new Api($_ENV['BOT_TOKEN']);

$bot->addCommands([
    StartCommand::class,
    LogoutCommand::class,
    HelpCommand::class,
]);
$bot->commandsHandler(true);

$update = $bot->getWebhookUpdate();

if ($update->has('message')) {
    $message = $update->getMessage();
    MessageHandler::handle($message, $bot);
} elseif ($update->has('callback_query')) {
    $callbackQuery = $update->getCallbackQuery();
    CallbackHandler::handle($callbackQuery);
}
