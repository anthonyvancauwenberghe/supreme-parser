<?php


namespace Supreme\Parser\Parsers;


use PHPHtmlParser\Dom\HtmlNode;
use Supreme\Parser\Abstracts\ResponseParser;
use Supreme\Parser\Traversers\RecursiveNodeWalker;

class SupremePreviewUrlsParser extends ResponseParser
{
    public function parse()
    {
        $traverser = new RecursiveNodeWalker($this->dom->root, "class", "inner-article");
        $results = $traverser->traverse();

        $urls = [];
        foreach ($results as $node) {
            $urls[] = $this->getUrlFromInnerArticle($node);
        }
        return $urls;
    }

    public function getUrlFromInnerArticle(HtmlNode $innerArticle): string
    {
        $child = $innerArticle->getChildren()[0];
        $tag = $child->tag;
        $url = $tag->getAttribute('href')['value'];
        return $url;
    }

}
