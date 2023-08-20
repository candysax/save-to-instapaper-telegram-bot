<?php

require_once __DIR__ . '/vendor/autoload.php';

use Telegram\Bot\Api;

if (file_exists('.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);;
    $dotenv->load();
}

$telegram = new Api($_ENV['BOT_TOKEN']);

$telegram->setWebhook(['url' => $_ENV['BOT_URL']]);
