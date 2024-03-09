<?php

namespace SaveToInstapaperBot\Processors;

use SaveToInstapaperBot\Adapters\TelegraphAdapter;
use SaveToInstapaperBot\Base\Bot;
use SaveToInstapaperBot\Base\Database;
use SaveToInstapaperBot\Helpers\AuthStage;
use SaveToInstapaperBot\Helpers\ErrorLogger;
use SaveToInstapaperBot\Services\Auth;
use Telegram\Bot\Actions;

class AuthProcessor extends BaseMessageProcessor
{
    protected const SUCCESSFUL = 200;

    public function processMessage(): void
    {
        switch (Database::get('auth_stage', $this->chatId)) {
            case AuthStage::AUTHORIZING_STARTED:
                $this->startAuthorization();
                break;

            case AuthStage::USERNAME_ENTERED:
                $this->processUsername($this->message->getText());
                break;

            case AuthStage::PASSWORD_ENTERED:
                $this->processPassword($this->message->getText());
                break;
        }
    }


    protected function startAuthorization(): void
    {
        Bot::api()->sendMessage([
            'chat_id' => $this->chatId,
            'text' => 'Enter your email:',
        ]);

        Database::set('auth_stage', AuthStage::USERNAME_ENTERED, $this->chatId);
    }

    protected function processUsername(string $username): void
    {
        Database::set('username', $username, $this->chatId);

        Bot::api()->sendMessage([
            'chat_id' => $this->chatId,
            'text' => 'Enter your password:',
        ]);

        Database::set('auth_stage', AuthStage::PASSWORD_ENTERED, $this->chatId);
    }

    protected function processPassword(string $password): void
    {
        $bot = Bot::api();

        try {
            $bot->sendChatAction([
                'chat_id' => $this->chatId,
                'action' => Actions::TYPING,
            ]);

            $response = Auth::login(Database::get('username', $this->chatId), $password);

            if ($response->getStatusCode() === static::SUCCESSFUL) {
                Database::set('password', $password, $this->chatId);

                $accountData = $this->getTelegraphAccountData();

                Database::set('access_token', $accountData['access_token'], $this->chatId);
                Database::set('auth_stage', AuthStage::AUTHORIZED, $this->chatId);

                $bot->sendMessage([
                    'chat_id' => $this->chatId,
                    'text' => '☑️ You have successfully logged in to your account.',
                ]);
                $bot->deleteMessage([
                    'chat_id' => $this->chatId,
                    'message_id' => $this->message->getMessageId(),
                ]);
                $bot->sendMessage([
                    'chat_id' => $this->chatId,
                    'text' => '⬇️ To save a message, just send it to the chat bot.',
                ]);
            }
        } catch (\Exception $e) {
            $statusCode = $e->getCode();

            if ($statusCode === static::INVALID_CREDENTIALS) {
                $bot->sendMessage([
                    'chat_id' => $this->chatId,
                    'text' => '❗ Invalid username or password. Please log in to your Instapaper account again.',
                ]);
            } else {
                ErrorLogger::sendDefaultError('auth process', $this->chatId, $e);
            }

            Database::set('auth_stage', AuthStage::AUTHORIZING_STARTED, $this->chatId);

            static::rerun($this->message);
        }
    }

    protected function getTelegraphAccountData()
    {
        $tgUserName = $this->getUsernameForTelegraph();

        $response = TelegraphAdapter::createAccount($tgUserName)->getBody();
        $data = json_decode($response, true);

        if (!$data['ok']) {
            throw new \Exception($data['error']);
        }

        return $data['result'];
    }

    protected function getUsernameForTelegraph(): string
    {
        if (isset($this->message->from->username)) {
             return $this->message->from->username;
        }

        return Database::get('username', $this->chatId);
    }
}
