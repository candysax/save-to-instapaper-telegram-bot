<?php

namespace Papergram\Base;

use GuzzleHttp\Exception\ClientException;
use Papergram\Adapters\InstapaperAdapter;
use Papergram\Base\Database;
use Papergram\Helpers\Stage;
use Papergram\Helpers\Text;
use stdClass;
use PHPOnCouch\Exceptions\CouchNotFoundException;
use Telegram\Bot\Actions;
use Telegram\Bot\Objects\Message;

class Auth
{
    const SUCCESSFUL = 200;
    const INVALID_CREDENTIALS = 403;

    public static function isLogged(string $chatId)
    {
        $db = Database::getInstance();
        
        try {
            $user = $db->getDoc($chatId);
            if ($user->stage !== Stage::AUTHORIZED) {
                return false;
            }
            return true;
        } catch (CouchNotFoundException $e) {
            return false;
        } catch (\Exception $e) {
            Bot::getInstance()->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Sorry, something went wrong. Please try again later.'
            ]);
        }
    }

    public static function set(string $key, int|string|bool $value, string $chatId)
    {
        $db = Database::getInstance();

        try {
            $user = $db->getDoc($chatId);
            $user->{$key} = $value;

            // try {
            $db->storeDoc($user);
            // } catch (\Exception $e) {
                // echo 'Ошибка при сохранении записи: ' . $e->getMessage();
            // }
        } catch (CouchNotFoundException $e) {
            $user = new stdClass();
            $user->_id = $chatId;
            $user->{$key} = $value;

            $db->storeDoc($user);
        } catch (\Exception $e) {
            Bot::getInstance()->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Sorry, something went wrong. Please try again later.'
            ]);
        }
    }

    public static function get(string $key, string $chatId)
    {
        $db = Database::getInstance();

        try {
            $user = $db->getDoc($chatId);
            return $user->{$key};
        } catch (\Exception $e) {
            Bot::getInstance()->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Sorry, something went wrong. Please try again later.'
            ]);
        }
    }

    public static function login(string $username, string $password)
    {
        return InstapaperAdapter::auth($username, $password);
    }

    public static function logout(string $chatId)
    {
        $db = Database::getInstance();        

        try {
            $user = $db->getDoc($chatId);
            $db->deleteDoc($user);
        } catch (\Exception $e) {
            Bot::getInstance()->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Sorry, something went wrong. Please try again later.'
            ]);
        }
    }

    public static function process(Message $message)
    {
        $bot = Bot::getInstance();
        $chatId = $message->getChat()->getId();
        $text = $message->getText();

        switch (Auth::get('stage', $chatId)) {
            case Stage::AUTHORIZING_STARTED:
                $bot->sendMessage([
                    'chat_id' => $chatId,
                    'text' => Text::enterUsernameMessage(),
                ]);

                Auth::set('stage', Stage::USERNAME_ENTERED, $chatId);
                break;
            
            case Stage::USERNAME_ENTERED:
                Auth::set('username', $text, $chatId);

                $bot->sendMessage([
                    'chat_id' => $chatId,
                    'text' => Text::enterPasswordMessage(),
                ]);

                Auth::set('stage', Stage::PASSWORD_ENTERED, $chatId);
                break;
            
            case Stage::PASSWORD_ENTERED:
                try {
                    $bot->sendChatAction([
                        'chat_id' => $chatId,
                        'action' => Actions::TYPING,
                    ]);
                    $response = Auth::login(Auth::get('username', $chatId), $text);
                    if ($response->getStatusCode() === static::SUCCESSFUL) {
                        Auth::set('password', $text, $chatId);
                        Auth::set('stage', Stage::AUTHORIZED, $chatId);
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
                    Auth::set('stage', Stage::AUTHORIZING_STARTED, $chatId);
                    static::process($message);
                }
                break;
        }
    }
}