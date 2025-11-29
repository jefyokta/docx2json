<?php

namespace Jefyokta\Docx2json\Context;

use Jefyokta\Docx2json\Contract\Resetable;

class Heading implements Resetable
{

    /**
     * @var array<string,int>
     */

    private static $headings = [];


    static  function set($key, $value)
    {

        static::$headings[$key] = $value;
    }

    static function getLevel($key){
        return static::$headings[$key] ;
    }

    static function has($key){

        return isset(static::$headings[$key]);

    }

    static function reset()  {
        static::$headings = [];
        
    }
}
