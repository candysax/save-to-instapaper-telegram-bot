<?php

namespace SaveToInstapaperBot\Processors;

use SaveToInstapaperBot\Adapters\TelegraphAdapter;
use SaveToInstapaperBot\Base\Bot;
use SaveToInstapaperBot\Base\Database;
use SaveToInstapaperBot\Helpers\AuthStage;
use SaveToInstapaperBot\Services\Auth;
use Telegram\Bot\Actions;
use Telegram\Bot\Objects\Message;

class AuthProcessor
{
    const SUCCESSFUL = 200;
    const INVALID_CREDENTIALS = 403;

    public static function processMessage(Message $message)
    {
        $bot = Bot::getInstance();
        $chatId = $message->getChat()->getId();
        $text = $message->getText();

        switch (Database::get('auth_stage', $chatId)) {
            case AuthStage::AUTHORIZING_STARTED:
                $bot->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Enter your email:',
                ]);

                Database::set('auth_stage', AuthStage::USERNAME_ENTERED, $chatId);
                break;

            case AuthStage::USERNAME_ENTERED:
                Database::set('username', $text, $chatId);

                $bot->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Enter your password:',
                ]);

                Database::set('auth_stage', AuthStage::PASSWORD_ENTERED, $chatId);
                break;

            case AuthStage::PASSWORD_ENTERED:
                try {
                    $bot->sendChatAction([
                        'chat_id' => $chatId,
                        'action' => Actions::TYPING,
                    ]);
                    $response = Auth::login(Database::get('username', $chatId), $text);
                    if ($response->getStatusCode() === static::SUCCESSFUL) {
                        Database::set('password', $text, $chatId);

                        $accountData = static::getTelegraphAccountData($message->from->username, $chatId);
                        Database::set('access_token', $accountData['access_token'], $chatId);

                        Database::set('auth_stage', AuthStage::AUTHORIZED, $chatId);

                        $bot->sendMessage([
                            'chat_id' => $chatId,
                            'text' => 'You have successfully logged in to your account.',
                        ]);
                        $bot->deleteMessage([
                            'chat_id' => $chatId,
                            'message_id' => $message->getMessageId(),
                        ]);
                    }
                } catch (\Exception $e) {
                    $statusCode = $e->getCode();
                    if ($statusCode === static::INVALID_CREDENTIALS) {
                        $bot->sendMessage([
                            'chat_id' => $chatId,
                            'text' => 'Invalid username or password. Please log in to your instapaper account again.',
                        ]);
                    } else {
                        $bot->sendMessage([
                            'chat_id' => $chatId,
                            'text' => 'Sorry, something went wrong. Please try again later.',
                        ]);
                    }
                    Database::set('auth_stage', AuthStage::AUTHORIZING_STARTED, $chatId);
                    static::processMessage($message);
                }
                break;
        }
    }

    private static function getTelegraphAccountData(string $tgUserName)
    {
        $response = TelegraphAdapter::createAccount($tgUserName)->getBody();
        $data = json_decode($response, true);

        if (!$data['ok']) {
            throw new \Exception($data['error']);
        }

        return $data['result'];
    }
}
