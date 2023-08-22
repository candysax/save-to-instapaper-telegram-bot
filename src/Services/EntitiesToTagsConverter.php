<?php

namespace SaveToInstapaperBot\Services;

use SaveToInstapaperBot\Helpers\Emojis;

class EntitiesToTagsConverter
{
    public function convert($entities, string $text)
    {
        $shift = 0;
        $searchText = $text;

        $startPosition = 0;
        $totalEmojisCount = 0;

        foreach ($entities as $entity) {
            $entityType = $entity->getType();
            $entityOffset = $entity->getOffset();

            $emojis = Emojis::count($searchText, $startPosition, $entityOffset);
            $totalEmojisCount += $emojis;

            $entityText = mb_substr($searchText, $entityOffset - $totalEmojisCount, $entity->getLength());
            $shift = strpos($text, $entityText, $shift);

            switch ($entityType) {
                case 'text_link':
                    $linkUrl = $entity->getUrl();
                    $replacement = '<a href="' . $linkUrl . '">' . $entityText . '</a>';
                    $text = substr_replace($text, $replacement, $shift, strlen($entityText));
                    $shift += strlen($replacement);
                    break;

                case 'url':
                    $replacement = '<a href="' . $entityText . '">' . $entityText . '</a>';
                    $text = substr_replace($text, $replacement, $shift, strlen($entityText));
                    $shift += strlen($replacement);
                    break;

                case 'phone':
                    $replacement = '<a href="tel:' . $entityText . '">' . $entityText . '</a>';
                    $text = substr_replace($text, $replacement, $shift, strlen($entityText));
                    $shift += strlen($replacement);
                    break;

                case 'email':
                    $replacement = '<a href="mailto:' . $entityText . '">' . $entityText . '</a>';
                    $text = substr_replace($text, $replacement, $shift, strlen($entityText));
                    $shift += strlen($replacement);
                    break;

                case 'bold':
                    $replacement = '<b>' . $entityText . '</b>';
                    $text = substr_replace($text, $replacement, $shift, strlen($entityText));
                    $shift += strlen($replacement);
                    break;

                case 'strikethrough':
                    $replacement = '<s>' . $entityText . '</s>';
                    $text = substr_replace($text, $replacement, $shift, strlen($entityText));
                    $shift += strlen($replacement);
                    break;

                case 'underline':
                    $replacement = '<u>' . $entityText . '</u>';
                    $text = substr_replace($text, $replacement, $shift, strlen($entityText));
                    $shift += strlen($replacement);
                    break;
            }

            $startPosition = $entityOffset;
        }

        return "<p>{$text}</p>";
    }
}
