<?php

namespace SaveToInstapaperBot\Enums;

enum AuthStage: int
{
    case AUTHORIZING_STARTED = 0;
    case USERNAME_ENTERED = 1;
    case PASSWORD_ENTERED = 2;
    case AUTHORIZED = 3;
}
