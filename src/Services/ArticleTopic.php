<?php

namespace SaveToInstapaperBot\Services;

class ArticleTopic
{
    protected const MAX_TOPIC_LENGTH = 30;

    public function __construct(protected string $text)
    {
        $this->text = $text;
    }

    public function generate(): string
    {
        $text = $this->text;

        $pos = mb_strpos($text, "\n");
        if ($pos !== false) {
            $text = mb_substr($text, 0, $pos);
        }

        $words = explode(' ', $text);

        $topic = '';

        foreach ($words as $i => $word) {
            if (mb_strlen($topic . $word) > static::MAX_TOPIC_LENGTH) {
                if ($i === 0) {
                    $topic .= mb_substr($word, 0, static::MAX_TOPIC_LENGTH - 3) . '...';
                }
                break;
            }
            $topic .= $word . ' ';
        }

        return trim($topic);
    }
}
