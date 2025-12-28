<?php

namespace Jefyokta\Docx2json\Utils;

class File
{

    public function __construct(private $fileName) {}


    function read()
    {
        return file_get_contents($this->fileName);
    }

    function readLines($flag = 0)
    {
        return file($this->fileName, $flag);
    }

    function getPath()
    {

        return $this->fileName;
    }

    function exists()
    {

        return file_exists($this->fileName);
    }
}
