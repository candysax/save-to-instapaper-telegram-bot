<?php

namespace SaveToInstapaperBot\Services;

use SaveToInstapaperBot\Adapters\TelegraphAdapter;
use Candysax\TelegraphNodeConverter\HTML;

class ArticlePage
{
    private string $topic;
    private string $text;
    private $forwardFromChat;
    private string $token;

    public function __construct(string $topic, string $text, $forwardFromChat, string $token)
    {
        $this->topic = $topic;
        $this->text = $text;
        $this->forwardFromChat = $forwardFromChat;
        $this->token = $token;
    }


    public function create(): ?string
    {
        $title = $this->generateArticleTitle(
            $this->forwardFromChat,
            $this->topic
        );

        $content = HTML::convertToNode($this->text)->json();

        $response = TelegraphAdapter::createPage(
            $title,
            $this->token,
            $content,
        )->getBody();

        $data = json_decode($response, true);

        if (!$data['ok']) {
            throw new \Exception($data['error']);
        }

        return $data['result']['url'];
    }


    private function generateArticleTitle($forwardFromChat, string $topic): string
    {
        if ($forwardFromChat) {
            return "{$forwardFromChat['title']} | {$topic}";
        }

        return "Personal post | {$topic}";
    }
}
