<?php

namespace SaveToInstapaperBot\Processors;

use Telegram\Bot\Objects\Message;

abstract class BaseMessageProcessor extends BaseProcessor
{
    protected const INVALID_CREDENTIALS = 401;

    protected Message $message;
    protected string $chatId;

    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->chatId = $message->getChat()->getId();
    }

    public static function run(...$params): static
    {
        $instance = new static(...$params);
        $instance->processMessage();

        return $instance;
    }

    public static function rerun(...$params): static
    {
        return static::run(...$params);
    }

    abstract public function processMessage(): void;
}
