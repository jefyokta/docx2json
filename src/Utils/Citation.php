<?php

namespace Jefyokta\Docx2json\Utils;

use Seboettg\CiteProc\CiteProc;

class Citation
{

    private $bib;
    private $hasNormalized = false;
    public function __construct(public $key, public $data = []) {}
    private function citationStyle()
    {
        return FileFinder::findInVendor("citation-style-language/styles/bibtex.csl");
    }
    function getKey()
    {
        if (!$this->hasNormalized) {
            $this->normilize();
        }
        return $this->key;
    }
    private function proc()
    {
        return new CiteProc($this->citationStyle()->read());
    }

    function  isDataError(): bool
    {

        return false;
    }
    protected function isCsl() {}
    function getBib()
    {

        if (!$this->hasNormalized) {
            $this->normilize();
        }
        return  $this->bib;
    }

    function normilize()
    {

        try {
            $html = $this->proc()->render($this->toObject($this->data));

            $bib = trim(str_replace(['<div class="csl-bib-body">', '<div class="csl-entry">', '</div>'], '', $html)) . "}";
            $this->normalizeKey($bib);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function normalizeKey($bib)
    {

        $start = (strpos($bib, "{") ?? 0) + 1;
        $end = strpos($bib, ",");

        $chars = str_split($bib);

        // var_dump($chars);
        $key = "";
        $res = '';
        foreach ($chars as $index => $char) {
            if ($index >= $start && $index < $end) {
                if ($char == " ") {
                    continue;
                }
                $key .= $char;
                $res .= $char;
            } else {
                $res .= $char;
            }
        }

        $key = $this->safeKey($key);
        $this->key = $key;

        $this->bib = $res;
    }

    private  function safeKey(string $key): string
    {
        $key = mb_strtolower($key, 'UTF-8');

        if (class_exists('Transliterator')) {
            $key = transliterator_transliterate(
                'Any-Latin; Latin-ASCII; [:Nonspacing Mark:] Remove;',
                $key
            );
        }

        $key = preg_replace('/[^a-z0-9:_-]/', '', $key);

        if ($key === '') {
            return 'ref';
        }

        return $key;
    }

    private  function toObject($data)
    {

        return [json_decode(json_encode($data))];
    }
}
