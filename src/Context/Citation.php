<?php

namespace Jefyokta\Docx2json\Context;

use Jefyokta\Docx2json\Contract\Context;
use Jefyokta\Docx2json\Contract\Resetable;
use Jefyokta\Docx2json\Utils\Citation as UtilsCitation;

class Citation implements Resetable, Context
{

    /**
     * @var UtilsCitation[]
     */
    private static $cites = [];

    function get() {}

    static function add(UtilsCitation $citation)
    {
        self::$cites[] = $citation;
    }

    static function getAll()
    {
        $merged = [];
        foreach (self::$cites as $cite) {
            $merged[$cite->getKey()] = $cite->getBib();
        }

        return $merged;
    }

    public static function reset()
    {
        self::$cites = [];
    }
}
