<?php

namespace SaveToInstapaperBot\Services;

use SaveToInstapaperBot\Adapters\TelegraphAdapter;
use SaveToInstapaperBot\Base\Bot;
use SaveToInstapaperBot\Base\Database;
use Candysax\TelegraphNodeConverter\HTML;

class ArticlePageGenerator
{
    private string $topic;
    private string $text;
    private $forwardFromChat;
    private string $chatId;

    public function __construct(string $topic, string $text, $forwardFromChat, string $chatId)
    {
        $this->topic = $topic;
        $this->text = $text;
        $this->forwardFromChat = $forwardFromChat;
        $this->chatId = $chatId;
    }


    public function createArticle()
    {
        $title = $this->generateArticleTitle(
            $this->forwardFromChat,
            $this->topic
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


    private function generateArticleTitle($forwardFromChat, string $topic)
    {
        if ($forwardFromChat) {
            return "{$forwardFromChat['title']} | {$topic}";
        }

        return "Personal post | {$topic}";
    }
}
