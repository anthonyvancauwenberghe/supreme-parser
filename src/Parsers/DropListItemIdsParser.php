<?php

namespace Supreme\Parser\Parsers;

use PHPHtmlParser\Dom\HtmlNode;
use Supreme\Parser\Abstracts\ResponseParser;

class DropListItemIdsParser extends ResponseParser
{
    public function parse(): array
    {
          return array_unique($this->recursiveWalk($this->dom->root));
    }

    public function recursiveWalk(HtmlNode $node, &$itemids = [],$counter = 0)
    {
        if ($node->getTag()->hasAttribute('data-itemid') && is_numeric($id = $node->getTag()->getAttribute('data-itemid')['value'])) {
            $itemids[] = (int) $id;
        }
        foreach ($node->getChildren() as $child) {
            if ($child instanceof HtmlNode)
                $this->recursiveWalk($child, $itemids,$counter);
        }
        return $itemids;
    }

}
