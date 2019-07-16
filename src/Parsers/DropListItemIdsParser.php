<?php

namespace Supreme\Parser\Parsers;

use PHPHtmlParser\Dom\HtmlNode;
use Supreme\Parser\Abstracts\ResponseParser;
use Supreme\Parser\Traversers\RecursiveNodeWalker;

class DropListItemIdsParser extends ResponseParser
{
    public function parse(): array
    {
        $traverser = new RecursiveNodeWalker($this->dom->root, "data-itemid", null);

        return array_unique($traverser->traverse(function (HtmlNode $node) {
            if (is_numeric($id = $node->getTag()->getAttribute('data-itemid')['value']))
                return (int) $id;
            return ;
        }));
    }

}
