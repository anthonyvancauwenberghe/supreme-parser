<?php


namespace Supreme\Parser\Parsers;


use PHPHtmlParser\Dom\HtmlNode;
use Supreme\Parser\Abstracts\ResponseParser;
use Supreme\Parser\Traversers\RecursiveNodeWalker;

class SupremePreviewItemParser extends ResponseParser
{
    public function parse()
    {
        $traverser = new RecursiveNodeWalker($this->dom->root, "data-images", null);
        $response = $traverser->traverseTillFirst();
        $json = $response->tag->getAttribute('data-images')['value'];
        $json = htmlspecialchars_decode($json);
        $decoded = json_decode($json);
        return $decoded;
    }

}
