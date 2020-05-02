<?php


namespace Supreme\Parser\Parsers;


use PHPHtmlParser\Dom\HtmlNode;
use Supreme\Parser\Traversers\RecursiveNodeWalker;

class LeftToDropIdParser extends DropListItemIdsParser
{
    public function parse(): array
    {
        $traverser = new RecursiveNodeWalker($this->dom->root, "class", "row l2d-category");

        $data = ($traverser->traverse(function (HtmlNode $node)  {
            $result = [];
            $category = $this->extractCategory($node);
            $ids = array_unique($this->extractItemIds($node));

            foreach ($ids as $id) {
                $result[$id]=$category;
            }
            return $result;
        }));

        $data =  $this->formatArray($data);
        return $data;
    }

    public function extractItemIds(HtmlNode $node)
    {
        $traverser = new RecursiveNodeWalker($node, "data-itemid");
        $nodes = $traverser->traverse();
        $ids = [];
        foreach ($nodes as $node) {
            if (($id = $node->getTag()->getAttribute('data-itemid')['value']) !== null) {
                $ids[] = (int)$id;
            }
        }
        return $ids;
    }

    public function extractCategory(HtmlNode $node): ?string
    {
        $traverser = new RecursiveNodeWalker($node, "class", "l2d-title");
        $node = $traverser->traverseTillFirst();
        $category = $this->firstNonEmptyTextNode($node) ?? "Unknown";
        return $category;
    }
}
