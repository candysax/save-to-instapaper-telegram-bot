<?php

namespace SaveToInstapaperBot\Services;

use SaveToInstapaperBot\Adapters\TelegraphAdapter;
use Candysax\TelegraphNodeConverter\HTML;

class ArticlePage
{
    public function __construct(
        protected string $topic,
        protected string $text,
        protected $forwardFromChat,
        protected string $token)
    {
        $this->topic = $topic;
        $this->text = $text;
        $this->forwardFromChat = $forwardFromChat;
        $this->token = $token;
    }

    public function create(): ?string
    {
        $response = TelegraphAdapter::createPage(
            $this->formatArticleTitle($this->forwardFromChat, $this->topic),
            $this->token,
            HTML::convertToNode($this->text)->json(),
        )->getBody();

        $data = json_decode($response->getContents(), true);

        if (!$data['ok']) {
            throw new \Exception($data['error']);
        }

        return $data['result']['url'];
    }


    private function formatArticleTitle($forwardFromChat, string $topic): string
    {
        if ($forwardFromChat) {
            return "{$forwardFromChat['title']} | {$topic}";
        }

        return "Personal post | {$topic}";
    }
}
