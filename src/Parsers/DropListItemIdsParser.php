<?php

namespace Supreme\Parser\Parsers;

use PHPHtmlParser\Dom\HtmlNode;
use Supreme\Parser\Abstracts\ResponseParser;

class DropListItemIdsParser extends ResponseParser
{

    protected $layersDeep = 0;

    public function parse(): array
    {
        $itemIds = [];

        /** @var HtmlNode $node */
        $node = $this->dom->getElementsByClass("masonry__container")->offsetGet(0);

        $mainItemDivs = $this->getMansoryItemDivs($node);

        foreach ($mainItemDivs as $mainItemDiv) {
            $card = $this->getCardCard2Div($mainItemDiv);
            $itemIds[] = intval($this->extractItemId($card));
        }
        return $itemIds;
    }

    protected function extractItemId(HtmlNode $cardcard2node)
    {
        if ($cardcard2node->getTag()->hasAttribute('data-itemid')) {
            return $cardcard2node->getTag()->getAttribute('data-itemid')['value'];
        }

        foreach ($cardcard2node->getChildren() as $child) {
            if ($child instanceof HtmlNode) {
                $result = $this->extractItemId($child);

                if ($result !== FALSE) {
                    $this->layersDeep = 0;
                    return $result;
                }

                $this->layersDeep++;
                if ($this->layersDeep === 500) {
                    $this->layersDeep = 0;
                    throw new \RuntimeException("Extracting itemid from cardnode failed. Maybe website changed?");
                }
            }
        }
        return false;
    }

    protected function getMainItemsDiv()
    {
        return $this->dom->getElementsByClass("masonry__container")->offsetGet(0);
    }

    protected function getMansoryItemDivs(HtmlNode $node)
    {
        $nodes = [];
        foreach ($node->getChildren() as $child) {
            if ($child instanceof HtmlNode && $child->getTag()->name() === 'div' && $child->getTag()->hasAttribute('data-masonry-filter') && $child->getTag()->getAttribute('data-masonry-filter')['value'] !== 'ads') {
                $nodes[] = $child;
            }
        }
        return $nodes;
    }

    protected function getCardCard2Div(HtmlNode $mansoryItemDiv)
    {
        foreach ($mansoryItemDiv->getChildren() as $child) {
            if ($child instanceof HtmlNode && $child->getTag()->name() === 'div') {
                return $child;
            }
        }
    }

}
