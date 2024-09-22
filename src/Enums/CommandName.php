<?php

namespace SaveToInstapaperBot\Enums;

enum CommandName: string
{
    case START = 'start';
    case HELP = 'help';
    case LOGOUT = 'logout';
}
