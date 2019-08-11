<?php

namespace Supreme\Parser\Parsers;

use Illuminate\Support\Str;
use PHPHtmlParser\Dom\HtmlNode;
use Supreme\Parser\Abstracts\ResponseParser;
use Supreme\Parser\Traversers\RecursiveNodeWalker;

class DropListItemIdsParser extends ResponseParser
{
    public function parse(): array
    {
        $traverser = new RecursiveNodeWalker($this->dom->root, "data-itemid", null);

        $data = ($traverser->traverse(function (HtmlNode $node) {
            if (is_numeric($id = $node->getTag()->getAttribute('data-itemid')['value'])) {
                return [((int)$id) => $this->getItemCategory($node)];
            }

            return [];
        }));
        return $this->formatArray($data);
    }

    protected function formatArray(array $data)
    {
        $output = [];

        foreach ($data as $someData) {
            foreach ($someData as $id => $category) {
                $output[$id] = $category;
            }
        }
        return $output;
    }

    protected function getItemCategory(HtmlNode $itemIdNode)
    {
        $parent = $itemIdNode->parent;
        $category = 'Unknown';
        while ($parent !== null) {
            if ($parent->getTag()->hasAttribute('data-masonry-filter')) {
                $category = $parent->getTag()->getAttribute('data-masonry-filter')['value'];
                $category = Str::replaceFirst('-','/',$category);
                break;
            }
            $parent = $parent->parent;
        }
        return $category;
    }

}
