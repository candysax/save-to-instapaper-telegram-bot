<?php

namespace SaveToInstapaperBot\Processors;

use Telegram\Bot\Objects\CallbackQuery;

abstract class BaseCallbackQueryProcessor extends BaseProcessor
{
    protected CallbackQuery $callbackQuery;
    protected string $chatId;

    public function __construct(CallbackQuery $callbackQuery)
    {
        $this->callbackQuery = $callbackQuery;
        $this->chatId = $callbackQuery->getMessage()->getChat()->getId();
    }

    public static function catch(...$params): static
    {
        $instance = new static(...$params);
        $instance->processCallbackQuery();

        return $instance;
    }

    abstract public function processCallbackQuery(): void;
}
