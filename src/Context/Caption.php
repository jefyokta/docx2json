<?php

namespace Jefyokta\Docx2json\Context;

use Jefyokta\Docx2json\Contract\Context;

class Caption implements Context{

    private static $captionStyleId = "Caption";

    static function getStyleId(){

        return static::$captionStyleId;
    }

    static function setStyleId($id){
        static::$captionStyleId = $id;
    }
};
