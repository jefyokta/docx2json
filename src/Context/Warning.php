<?php

namespace Jefyokta\Docx2json\Context;

use Jefyokta\Docx2json\Contract\Context;
use Jefyokta\Docx2json\Contract\Resetable;

class Warning implements Resetable,Context
{
    private static $warnings = [];

    public static function reset() {

        self::$warnings =[];
    }

    static function getAll(){

        return self::$warnings;
    }

    static function set($name, $message)  {
        self::$warnings[$name] = $message;        
    }
};
