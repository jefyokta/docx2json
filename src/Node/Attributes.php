<?php
namespace Jefyokta\Docx2json\Node;
class Attributes
{

    private $attrs = [];

    public function __set($name, $value) {
        $this->attrs[$name] = $value;
    }

    public function __get($name)
    {

        return $this->attrs[$name] ?? null;
        
    }

    public function toArray(){
        return $this->attrs;
    }
};
