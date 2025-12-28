<?php

namespace Jefyokta\Docx2json\Utils;

class FileFinder
{


    static function findInVendor($fileName)
    {

        return new File(self::getVendorPath() . $fileName);
    }


    private static function getVendorPath()
    {

        $asProject = __DIR__ . "/../../vendor/";
        $asVendor = __DIR__ . "/../../../vendor/";

        return file_exists($asProject . "autoload.php") ? $asProject : $asVendor;
    }
};
