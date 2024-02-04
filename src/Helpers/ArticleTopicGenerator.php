<?php

namespace SaveToInstapaperBot\Helpers;

class ArticleTopicGenerator
{
    public static function generate(string $text)
    {
        $maxTopicLength = 30;

        $pos = mb_strpos($text, "\n");
        if ($pos !== false) {
            $text = mb_substr($text, 0, $pos);
        }

        $words = explode(' ', $text);

        $topic = '';

        foreach ($words as $i => $word) {
            if (mb_strlen($topic . $word) > $maxTopicLength) {
                if ($i === 0) {
                    $topic .= mb_substr($word, 0, $maxTopicLength - 3) . '...';
                }
                break;
            }
            $topic .= $word . ' ';
        }

        return trim($topic);
    }
}
