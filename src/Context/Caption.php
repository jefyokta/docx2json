<?php

namespace Jefyokta\Docx2json\Context;

class Caption {

    private static $captionStyleId = "Caption";

    static function getStyleId(){

        return static::$captionStyleId;
    }

    static function setStyleId($id){
        static::$captionStyleId = $id;
    }
};
