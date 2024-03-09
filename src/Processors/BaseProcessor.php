<?php

namespace SaveToInstapaperBot\Processors;

use SaveToInstapaperBot\Adapters\InstapaperAdapter;
use SaveToInstapaperBot\Base\Bot;
use SaveToInstapaperBot\Base\Database;
use SaveToInstapaperBot\Services\ArticlePage;
use Telegram\Bot\Actions;

abstract class BaseProcessor
{
    protected const SUCCESSFUL = 200;

    protected function processLink(string $url): void
    {
        Bot::api()->sendChatAction([
            'chat_id' => $this->chatId,
            'action' => Actions::TYPING,
        ]);

        if ($this->saveLink($url)) {
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
            Database::get('telegraph_access_token', $this->chatId))
        )->create();

        if ($this->saveLink($url)) {
            Bot::api()->sendMessage([
                'chat_id' => $this->chatId,
                'text' => '✅ Message has been added to your Instapaper.',
            ]);
        }
    }


    protected function saveLink(string $url): bool
    {
        $response = InstapaperAdapter::save($url, [
            'token' => Database::get('token', $this->chatId),
            'token_secret' => Database::get('token_secret', $this->chatId),
        ]);

        return $response->getStatusCode() == static::SUCCESSFUL;
    }
}
