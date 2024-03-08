<?php

namespace SaveToInstapaperBot\Processors;

use Psr\Http\Message\ResponseInterface;
use SaveToInstapaperBot\Adapters\InstapaperAdapter;
use SaveToInstapaperBot\Base\Bot;
use SaveToInstapaperBot\Base\Database;
use SaveToInstapaperBot\Services\ArticlePage;
use Telegram\Bot\Actions;

abstract class BaseProcessor
{
    protected function processLink(string $url): void
    {
        Bot::api()->sendChatAction([
            'chat_id' => $this->chatId,
            'action' => Actions::TYPING,
        ]);

        $response = $this->saveLink($url);

        if ($response->getStatusCode() === static::SUCCESSFUL) {
            Bot::api()->sendMessage([
                'chat_id' => $this->chatId,
                'text' => '✅ Link has been added to your Instapaper.',
            ]);
        }
    }


    protected function processText(string $topic, string $text, $forwardFromChat): void
    {
        Bot::api()->sendChatAction([
            'chat_id' => $this->chatId,
            'action' => Actions::TYPING,
        ]);

        $url = (new ArticlePage(
            $topic,
            $text,
            $forwardFromChat,
            Database::get('access_token', $this->chatId))
        )->create();

        $response = $this->saveLink($url);

        if ($response->getStatusCode() === static::SUCCESSFUL) {
            Bot::api()->sendMessage([
                'chat_id' => $this->chatId,
                'text' => '✅ Message has been added to your Instapaper.',
            ]);
        }
    }


    protected function saveLink(string $url): ResponseInterface
    {
        return InstapaperAdapter::save($url, [
            'username' => Database::get('username', $this->chatId),
            'password' => Database::get('password', $this->chatId),
        ]);
    }
}
