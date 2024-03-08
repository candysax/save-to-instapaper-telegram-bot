<?php

namespace SaveToInstapaperBot\Services;

use SteppingHat\EmojiDetector\EmojiDetector;

class EmojisCounter
{
    public static function count(string $text, int $startPosition, int $endPosition): ?int
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
