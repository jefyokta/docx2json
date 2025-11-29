<?php

namespace Jefyokta\Docx2json\Context;

use Jefyokta\Docx2json\Contract\Resetable;
use Jefyokta\Docx2json\Utils\Citation as UtilsCitation;

class Citation implements Resetable
{

    private static $cites = [];

    function get() {}

    static function add(UtilsCitation $citation)
    {
        self::$cites[] = $citation;
    }

    static function getAll(){

        return self::$cites;
    }

    public static function reset()
    {
        self::$cites = [];
    }
}
