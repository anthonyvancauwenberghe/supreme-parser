<?php

namespace Supreme\Parser\Parsers;

use Illuminate\Support\Str;
use PHPHtmlParser\Dom\HtmlNode;
use Supreme\Parser\Abstracts\ResponseParser;
use Supreme\Parser\Traversers\RecursiveNodeWalker;
use Supreme\Parser\Traversers\RecursiveNodeWalkerTextFinder;

class SeasonListItemIdsParser extends ResponseParser
{
    public function parse(): array
    {
        $traverser = new RecursiveNodeWalker($this->dom->root, "class", 'row l2d-category');

        $categoryNodes = $traverser->traverse();
        $output = [];
        foreach ($categoryNodes as $node) {
            $output[$this->parseCategoryFromNode($node)] = $this->parseIdsFromNode($node);
        }
        return $output;
    }

    protected function parseCategoryFromNode(HtmlNode $node): string
    {
        $traverser = new RecursiveNodeWalker($node, "class", 'l2d-title');
        $node = $traverser->traverse();
        $traverser = new RecursiveNodeWalkerTextFinder($node[0]);
        $category = $traverser->traverse();
        return $category[0];
    }

    protected function parseIdsFromNode(HtmlNode $node): array
    {
        $traverser = new RecursiveNodeWalker($node, "data-itemid", null);

        $data = ($traverser->traverse(function (HtmlNode $node) {
            if (is_numeric($id = $node->getTag()->getAttribute('data-itemid')['value'])) {
                return (int) $id;
            }
            return null;
        }));

        return array_unique($data);
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
                $category = Str::replaceFirst('-', '/', $category);
                break;
            }
            $parent = $parent->parent;
        }
        return $category;
    }

}
