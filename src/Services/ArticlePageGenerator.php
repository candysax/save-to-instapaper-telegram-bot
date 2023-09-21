<?php

namespace SaveToInstapaperBot\Services;

use SaveToInstapaperBot\Adapters\TelegraphAdapter;
use SaveToInstapaperBot\Base\Database;
use Candysax\TelegraphNodeConverter\HTML;

class ArticlePageGenerator
{
    private string $text;
    private $forwardFromChat;
    private int $date;
    private string $chatId;

    public function __construct(string $text, $forwardFromChat, int $date, string $chatId)
    {
        $this->text = $text;
        $this->forwardFromChat = $forwardFromChat;
        $this->date = $date;
        $this->chatId = $chatId;
    }


    public function createArticle()
    {
        $title = $this->generateArticleTitle(
            $this->forwardFromChat,
            $this->date
        );

        $content = HTML::convertToNode($this->text)->json();

        $response = TelegraphAdapter::createPage(
            $title,
            Database::get('access_token', $this->chatId),
            $content,
        )->getBody();

        $data = json_decode($response, true);

        if (!$data['ok']) {
            throw new \Exception($data['error']);
        }

        return $data['result']['url'];
    }


    private function generateArticleTitle($forwardFromChat, int $date)
    {
        $datetime = date('d.m.Y H:i', $date);

        if ($forwardFromChat) {
            $channelName = $forwardFromChat['title'] . ' ';

            return "Telegram post from {$channelName}({$datetime})";
        }

        return "Personal Telegram post ({$datetime})";
    }
}
