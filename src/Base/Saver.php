<?php

namespace Papergram\Base;

use Papergram\Adapters\InstapaperAdapter;
use Papergram\Helpers\Stage;
use Telegram\Bot\Actions;
use Telegram\Bot\Objects\Message;

class Saver
{
    const SUCCESSFUL = 201;
    const INVALID_CREDENTIALS = 403;

    public static function save(Message $message)
    {
        $bot = Bot::getInstance();
        $chatId = $message->getChat()->getId();
        $url = $message->getText();

        try {
            $bot->sendChatAction([
                'chat_id' => $chatId,
                'action' => Actions::TYPING,
            ]);
            $response = InstapaperAdapter::save($url, [
                'username' => Auth::get('username', $chatId),
                'password' => Auth::get('password', $chatId),
            ]);
            
            if ($response->getStatusCode() === static::SUCCESSFUL) {
                $bot->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Link was added to your Instapaper.',
                ]);
            }
        } catch (\Exception $e) {
            $statusCode = $e->getCode();
            if ($statusCode === static::INVALID_CREDENTIALS) {
                $bot->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Invalid username or password. Please log in to your instapaper account again.',
                ]);
                Auth::logout($chatId);
                Auth::set('stage', Stage::AUTHORIZING_STARTED, $chatId);
                Auth::process($message);
            } else {
                $bot->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Sorry, something went wrong. Please try again later.',
                ]);
            }
        }
    }
}
