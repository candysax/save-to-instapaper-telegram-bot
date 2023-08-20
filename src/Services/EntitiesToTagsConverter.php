<?php

namespace SaveToInstapaperBot\Services;

class EntitiesToTagsConverter
{
    public function convert($entities, string $text)
    {
        $offset = 0;
        $searchText = $text;

        foreach ($entities as $entity) {
            $entityText = mb_substr($searchText, $entity->getOffset(), $entity->getLength());
            $offset = strpos($text, $entityText, $offset);

            $entityType = $entity->getType();
            switch ($entityType) {
                case 'text_link':
                    $linkUrl = $entity->getUrl();
                    $replacement = '<a href="' . $linkUrl . '">' . $entityText . '</a>';
                    $text = substr_replace($text, $replacement, $offset, strlen($entityText));
                    break;

                case 'url':
                    $replacement = '<a href="' . $entityText . '">' . $entityText . '</a>';
                    $text = substr_replace($text, $replacement, $offset, strlen($entityText));
                    break;

                case 'phone':
                    $replacement = '<a href="tel:' . $entityText . '">' . $entityText . '</a>';
                    $text = substr_replace($text, $replacement, $offset, strlen($entityText));
                    break;

                case 'email':
                    $replacement = '<a href="mailto:' . $entityText . '">' . $entityText . '</a>';
                    $text = substr_replace($text, $replacement, $offset, strlen($entityText));
                    break;

                case 'bold':
                    $replacement = '<b>' . $entityText . '</b>';
                    $text = substr_replace($text, $replacement, $offset, strlen($entityText));
                    break;

                case 'strikethrough':
                    $replacement = '<s>' . $entityText . '</s>';
                    $text = substr_replace($text, $replacement, $offset, strlen($entityText));
                    break;

                case 'underline':
                    $replacement = '<u>' . $entityText . '</u>';
                    $text = substr_replace($text, $replacement, $offset, strlen($entityText));
                    break;
            }

            $offset += strlen($replacement);
        }

        return "<p>{$text}</p>";
    }
}
