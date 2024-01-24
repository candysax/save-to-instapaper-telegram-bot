<?php

namespace SaveToInstapaperBot\Helpers;

use SteppingHat\EmojiDetector\EmojiDetector;

class Emojis
{
    public static function count($text, $startPosition, $endPosition)
    {
        $text = mb_substr($text, $startPosition, $endPosition - $startPosition);

        $detector = new EmojiDetector();
        $emojis = $detector->detect($text, false);

        return array_reduce($emojis, function ($sum, $emoji) {
            $shift = count($emoji->getHexCodes());
            if ($shift > 1) {
                $shift -= 1;
            }
            $sum += $shift;
            return $sum;
        });
    }
}
