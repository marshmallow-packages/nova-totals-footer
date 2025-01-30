<?php

namespace Marshmallow\NovaTotalsFooter;

class NovaTotalsFooter
{
    public static bool $hideHeader = false;

    public static function hideHeader()
    {
        self::$hideHeader = true;
    }
}
