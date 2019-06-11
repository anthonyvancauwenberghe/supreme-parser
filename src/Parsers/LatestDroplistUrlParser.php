<?php


namespace Supreme\Parser\Parsers;

use PHPHtmlParser\Dom\HtmlNode;
use Supreme\Parser\Abstracts\ResponseParser;

class LatestDroplistUrlParser extends ResponseParser
{
    public function parse()
    {
        $div = $this->dom->getElementById("box-latest");

        if ($div !== null) {
            foreach ($div->getChildren() as $child) {
                if ($child instanceof HtmlNode && $child->tag->hasAttribute('href') && array_key_exists('value', $child->tag->getAttribute('href'))) {
                    return $child->tag->getAttribute('href')['value'];
                }
            }
        }
        throw new \Exception("Error parsing latest droplist url");
    }

}