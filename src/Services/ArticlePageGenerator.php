<?php

namespace SaveToInstapaperBot\Services;

use SaveToInstapaperBot\Adapters\TelegraphAdapter;
use SaveToInstapaperBot\Base\Database;
use SaveToInstapaperBot\Services\TextToNodeConverter;

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

        $content = $this->generateArticleContent($this->text);

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


    private function generateArticleContent(string $text)
    {
        $textToNodeConverter = new TextToNodeConverter();
        $template = $textToNodeConverter->convert($text);

        return json_encode($template);
    }
}
