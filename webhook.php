<?php

require_once __DIR__ . '/vendor/autoload.php';

use Telegram\Bot\Api;

if (file_exists('.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);;
    $dotenv->load();
}

$telegram = new Api($_ENV['BOT_TOKEN']);

$telegram->setWebhook(['url' => 'https://1033-188-32-78-82.ngrok-free.app/']);
