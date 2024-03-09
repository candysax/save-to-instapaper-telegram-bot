<?php

namespace SaveToInstapaperBot\Processors;

use SaveToInstapaperBot\Base\Bot;
use SaveToInstapaperBot\Base\Database;
use SaveToInstapaperBot\Helpers\AuthStage;
use SaveToInstapaperBot\Helpers\ErrorLogger;
use SaveToInstapaperBot\Services\ArticleTopic;
use SaveToInstapaperBot\Services\Auth;
use SaveToInstapaperBot\Services\EmojisCounter;
use SaveToInstapaperBot\Services\EntitiesToTagsConverter;
use Telegram\Bot\Actions;
use Telegram\Bot\Keyboard\Keyboard;

class SaverMessageProcessor extends BaseMessageProcessor
{
    public function processMessage(): void
    {
        $bot = Bot::api();

        if ($this->message->has('caption')) {
            $text = $this->message->getCaption();
            $entities = $this->message->getCaptionEntities();
        } elseif ($this->message->has('text')) {
            $text = $this->message->getText();
            $entities = $this->message->getEntities();
        } else {
            return;
        }

        $entitiesConverter = new EntitiesToTagsConverter();

        $topic = (new ArticleTopic($text))->generate();
        $urls = $this->getUrls($entities, $text);

        $isTextLink = count($urls) === 1 && strtolower($urls[0]) === strtolower(preg_replace('/\s+/', '', $text));

        try {
            if ($isTextLink) {
                $this->processLink($text);
            } elseif (count($urls)) {
                $text = $entitiesConverter->convert($entities, $text);

                $bot->sendChatAction([
                    'chat_id' => $this->chatId,
                    'action' => Actions::TYPING,
                ]);

                $replyMarkup = $this->addKeyboard($urls);

                Database::set('temp', json_encode([
                    'forwardFromChat' => $this->message->getForwardFromChat(),
                    'text' => $text,
                    'topic' => $topic,
                    'links' => $urls,
                ]), $this->chatId);

                $listOfUrls = $this->formatUrlRows($urls);
                $bot->sendMessage([
                    'chat_id' => $this->chatId,
                    'text' => "The following links were found in the text:\n{$listOfUrls}\n\nDo you want to save only the link or the whole text?",
                    'disable_web_page_preview' => true,
                    'reply_markup' => $replyMarkup,
                ]);
            } else {
                $text = $entitiesConverter->convert($entities, $text);

                $this->processText(
                    $topic,
                    $text,
                    $this->message->getForwardFromChat()
                );
            }
        } catch (\Exception $e) {
            $statusCode = $e->getCode();

            if ($statusCode === static::INVALID_CREDENTIALS) {
                $bot->sendMessage([
                    'chat_id' => $this->chatId,
                    'text' => '❗ Invalid username or password. Please log in to your Instapaper account again.',
                ]);

                Auth::logout($this->chatId);
                Database::set('auth_stage', AuthStage::AUTHORIZING_STARTED, $this->chatId);

                AuthProcessor::run($this->message);
            } else {
                ErrorLogger::sendDefaultError('save process', $this->chatId, $e);
            }
        }
    }

    protected function getUrls($entities, string $text): array
    {
        $urls = [];

        $startPosition = 0;
        $totalEmojisCount = 0;
        foreach ($entities as $entity) {
            $entityOffset = $entity->getOffset();

            $emojisCount = EmojisCounter::count($text, $startPosition, $entityOffset);
            $totalEmojisCount += $emojisCount;

            if ($entity->getType() === 'text_link') {
                $urls[] = $entity->getUrl();
            } elseif ($entity->getType() === 'url') {
                $urls[] = mb_substr($text, $entityOffset - $totalEmojisCount, $entity->getLength());
            }

            $startPosition = $entityOffset;
        }

        return $urls;
    }


    protected function formatUrlRows(array $urls): string
    {
        $result = [];

        foreach ($urls as $index => $value) {
            $result[] = ($index + 1) . ". {$value}";
        }

        return implode("\n", $result);
    }


    protected function formatLinkButtons(array $urls): array
    {
        $keyboardLinkButtons = [];

        for ($i = 0; $i < count($urls); $i++) {
            $keyboardLinkButtons[] = Keyboard::button([
                'text' => "Save #" . ($i + 1),
                'callback_data' => "add_link {$i}",
            ]);
        }

        return $keyboardLinkButtons;
    }

    protected function addKeyboard($urls): Keyboard
    {
        $keyboardLinkButtons = $this->formatLinkButtons($urls);

        $replyMarkup = Keyboard::make()->inline()->row([
            ...$keyboardLinkButtons,
        ])->row([
            Keyboard::button([
                'text' => 'Save the whole text',
                'callback_data' => 'add_text',
            ]),
        ]);
        $replyMarkup->setOneTimeKeyboard(true);
        $replyMarkup->setResizeKeyboard(true);

        return $replyMarkup;
    }
}
