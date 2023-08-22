<?php

namespace SaveToInstapaperBot\Services;

use SaveToInstapaperBot\Base\Bot;

class EntitiesToTagsConverter
{
    public function convert($entities, string $text, $chatId = 0)
    {
        $shift = 0;
        $searchText = $text;

        // $emojis = $this->getEmojis($text);
        // $emojiIndex = 0;
        // Bot::getInstance()->sendMessage([
        //     'chat_id' => $chatId,
        //     'text' => json_encode($emojis),
        // ]);
        foreach ($entities as $entity) {
            $entityType = $entity->getType();
            // if ($entityOffset <= $emojis[$emojiIndex]['position']) {
            //     $emojiIndex++;
            //     $entityOffset -= $emojiIndex;
            // }
            $entityText = mb_substr($searchText, $entity->getOffset(), $entity->getLength());
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
        }

        return "<p>{$text}</p>";
    }


    private function getEmojis(string $text)
    {
        $emojiRegex = '/(\p{Emoji})/u';
        mb_ereg_search_init($text, $emojiRegex);

        $positions = [];
        while ($result = mb_ereg_search_pos()) {
            $positions[] = [
                'emoji' => mb_substr($text, $result[0], $result[1] - $result[0]),
                'start' => $result[0],
                'end' => $result[1]
            ];
        }

        return $positions;
    }
}
