<?php

require_once __DIR__ . '/vendor/autoload.php';

use SaveToInstapaperBot\Commands\HelpCommand;
use SaveToInstapaperBot\Commands\LogoutCommand;
use SaveToInstapaperBot\Commands\StartCommand;
use SaveToInstapaperBot\Handlers\CallbackHandler;
use SaveToInstapaperBot\Handlers\MessageHandler;
use SaveToInstapaperBot\Base\Bot;
use SaveToInstapaperBot\Helpers\ErrorLogger;

if (file_exists('.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);;
    $dotenv->load();
}

$bot = Bot::api();

$bot->addCommands([
    StartCommand::class,
    LogoutCommand::class,
    HelpCommand::class,
]);
$bot->commandsHandler(true);

$update = $bot->getWebhookUpdate();

try {
    if ($update->has('message')) {
        $message = $update->getMessage();
        (MessageHandler::start())->handle($message);
    } elseif ($update->has('callback_query')) {
        $callbackQuery = $update->getCallbackQuery();
        (CallbackHandler::start())->handle($callbackQuery);
    }
} catch (\Exception $exception) {
    ErrorLogger::sendDefaultError(
        'global',
        $update->has('message') ? $update->getMessage()->getChat()->getId() : $update->getCallbackQuery()->getMessage()->getChat()->getId(),
        $exception
    );
}
