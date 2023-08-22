<?php

namespace SaveToInstapaperBot\Processors;

use SaveToInstapaperBot\Adapters\InstapaperAdapter;
use SaveToInstapaperBot\Helpers\AuthStage;
use Telegram\Bot\Actions;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\CallbackQuery;
use SaveToInstapaperBot\Base\Bot;
use SaveToInstapaperBot\Base\Database;
use SaveToInstapaperBot\Helpers\Emojis;
use SaveToInstapaperBot\Services\EntitiesToTagsConverter;
use SaveToInstapaperBot\Services\ArticlePageGenerator;
use SaveToInstapaperBot\Services\Auth;

class SaverProcessor
{
    const SUCCESSFUL = 201;
    const INVALID_CREDENTIALS = 403;


    public static function processMessage(Message $messageInfo)
    {
        $bot = Bot::getInstance();
        $chatId = $messageInfo->getChat()->getId();

        if ($messageInfo->has('caption')) {
            $text = $messageInfo->getCaption();
            $entities = $messageInfo->getCaptionEntities();
        } else {
            $text = $messageInfo->getText();
            $entities = $messageInfo->getEntities();
        }

        $entitiesConverter = new EntitiesToTagsConverter();

        $urls = static::getUrls($entities, $text, $chatId);

        // $bot->sendMessage([
        //     'chat_id' => $chatId,
        //     'text' => $text,
        // ]);

        $isTextLink = count($urls) === 1 && strtolower($urls[0]) === strtolower(preg_replace('/\s+/', '', $text));

        try {
            if ($isTextLink) {
                $bot->sendChatAction([
                    'chat_id' => $chatId,
                    'action' => Actions::TYPING,
                ]);

                $response = static::saveLink($text, $chatId);

                if ($response->getStatusCode() === static::SUCCESSFUL) {
                    $bot->sendMessage([
                        'chat_id' => $chatId,
                        'text' => '✅ Link has been added to your Instapaper.',
                    ]);
                }
            } elseif (count($urls)) {
                $text = $entitiesConverter->convert($entities, $text, $chatId);

                $bot->sendChatAction([
                    'chat_id' => $chatId,
                    'action' => Actions::TYPING,
                ]);

                $keyboardLinkButtons = static::formatLinkButtons($urls);

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

                Database::set('temp', json_encode([
                    'forwardFromChat' => $messageInfo->getForwardFromChat(),
                    'date' => $messageInfo->getDate(),
                    'text' => $text,
                    'links' => $urls,
                ]), $chatId);

                $listOfUrls = static::formatUrlRows($urls);
                $bot->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "The following links were found in the text:\n{$listOfUrls}\n\nDo you want to save only the link or the whole text?",
                    'disable_web_page_preview' => true,
                    'reply_markup' => $replyMarkup,
                ]);
            } else {
                $text = $entitiesConverter->convert($entities, $text);
                static::processText(
                    $text,
                    $messageInfo->getForwardFromChat(),
                    $messageInfo->getDate(),
                    $chatId
                );
            }
        } catch (\Exception $e) {
            $statusCode = $e->getCode();
            if ($statusCode === static::INVALID_CREDENTIALS) {
                $bot->sendMessage([
                    'chat_id' => $chatId,
                    'text' => '❗ Invalid username or password. Please log in to your instapaper account again.',
                ]);

                Auth::logout($chatId);
                Database::set('auth_stage', AuthStage::AUTHORIZING_STARTED, $chatId);

                AuthProcessor::processMessage($messageInfo);
            } else {
                $bot->sendMessage([
                    'chat_id' => $chatId,
                    'text' => '❗ Sorry, something went wrong. Please try again later.',
                ]);
            }
        }
    }


    public static function processLink(string $url, string $chatId)
    {
        Bot::getInstance()->sendChatAction([
            'chat_id' => $chatId,
            'action' => Actions::TYPING,
        ]);

        $response = static::saveLink($url, $chatId);

        if ($response->getStatusCode() === static::SUCCESSFUL) {
            Bot::getInstance()->sendMessage([
                'chat_id' => $chatId,
                'text' => '✅ Link has been added to your Instapaper.',
            ]);
        }
    }


    public static function processText(string $text, $forwardFromChat, int $date, string $chatId)
    {
        Bot::getInstance()->sendChatAction([
            'chat_id' => $chatId,
            'action' => Actions::TYPING,
        ]);

        $articlePageGenerator = new ArticlePageGenerator($text, $forwardFromChat, $date,  $chatId);
        $url = $articlePageGenerator->createArticle();

        $response = static::saveLink($url, $chatId);

        if ($response->getStatusCode() === static::SUCCESSFUL) {
            Bot::getInstance()->sendMessage([
                'chat_id' => $chatId,
                'text' => '✅ Message has been added to your Instapaper.',
            ]);
        }
    }


    public static function processCallbackQuery(CallbackQuery $callbackQuery)
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();

        $data = explode(' ', $callbackQuery->getData());
        $savingType = $data[0];
        $urlIndex = $data[1] ?? '';

        $temp = json_decode(Database::get('temp', $chatId), true);

        if ($savingType === 'add_link') {
            static::processLink($temp['links'][$urlIndex], $chatId);
        } elseif ($savingType === 'add_text') {
            static::processText($temp['text'], $temp['forwardFromChat'], $temp['date'], $chatId);
        } else {
            Bot::getInstance()->sendMessage([
                'chat_id' => $chatId,
                'text' => "Sorry. The specified type of saving is incorrect.",
            ]);
        }

        Bot::getInstance()->deleteMessage([
            'chat_id' => $chatId,
            'message_id' => $callbackQuery->getMessage()->getMessageId(),
        ]);
    }


    private static function saveLink(string $url, string $chatId)
    {
        return InstapaperAdapter::save($url, [
            'username' => Database::get('username', $chatId),
            'password' => Database::get('password', $chatId),
        ]);
    }


    private static function getUrls($entities, string $text, $chatId = 0)
    {
        $urls = [];
        $startPosition = 0;
        $totalEmojisCount = 0;
        foreach ($entities as $entity) {
            $entityOffset = $entity->getOffset();

            $emojis = Emojis::count($text, $startPosition, $entityOffset);
            // Bot::getInstance()->sendMessage([
            //     'chat_id' => $chatId,
            //     'text' => json_encode($entity->getType()),
            // ]);

            $totalEmojisCount += $emojis;

            if ($entity->getType() === 'text_link') {
                $urls[] = $entity->getUrl();
            } elseif ($entity->getType() === 'url') {
                $urls[] = mb_substr($text, $entityOffset - $totalEmojisCount, $entity->getLength());
            }

            $startPosition = $entityOffset;
        }

        return $urls;
    }


    private static function formatUrlRows(array $urls) {
        $result = [];

        foreach ($urls as $index => $value) {
            $result[] = ($index + 1) . ". {$value}";
        }

        return implode("\n", $result);
    }


    private static function formatLinkButtons(array $urls) {
        $keyboardLinkButtons = [];

        for ($i = 0; $i < count($urls); $i++) {
            $keyboardLinkButtons[] = Keyboard::button([
                'text' => "Save #" . ($i + 1),
                'callback_data' => "add_link {$i}",
            ]);
        }

        return $keyboardLinkButtons;
    }
}
