<?php

namespace SaveToInstapaperBot\Processors;

use SaveToInstapaperBot\Base\Bot;
use SaveToInstapaperBot\Base\Database;

class SaverCallbackQueryProcessor extends BaseCallbackQueryProcessor
{
    public function processCallbackQuery(): void
    {
        $data = explode(' ', $this->callbackQuery->getData());
        $savingType = $data[0];
        $urlIndex = $data[1] ?? '';

        $temp = json_decode(Database::get('temp', $this->chatId), true);

        if ($savingType === 'add_link') {
            $this->processLink($temp['links'][$urlIndex]);
        } elseif ($savingType === 'add_text') {
            $this->processText($temp['topic'], $temp['text'], $temp['forwardFromChat']);
        } else {
            Bot::api()->sendMessage([
                'chat_id' => $this->chatId,
                'text' => '❗ Sorry, the specified type of saving is incorrect.',
            ]);
        }

        Bot::api()->deleteMessage([
            'chat_id' => $this->chatId,
            'message_id' => $this->callbackQuery->getMessage()->getMessageId(),
        ]);
    }
}
