<?php

namespace SaveToInstapaperBot\Handlers;

abstract class BaseHandler
{
    public static function start(): static
    {
        return new static();
    }
}
