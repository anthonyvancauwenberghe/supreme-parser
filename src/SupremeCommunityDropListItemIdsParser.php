<?php

namespace Supreme\Parser;

use PHPHtmlParser\Dom\HtmlNode;
use Supreme\Parser\Abstracts\HtmlParser;
use Supreme\Parser\Resolvers\LatestDropDateResolver;

class SupremeCommunityDropListItemIdsParser extends HtmlParser
{
    protected $baseUrl = "https://www.supremecommunity.com/season/spring-summer2019/droplist";

    public function __construct(string $date)
    {
        parent::__construct("/$date/");
    }

    public function parse(): array
    {
        $urls = [];

        $itemIds = [];

        /** @var HtmlNode $node */
        $node = $this->dom->getElementsByClass("masonry__container")->offsetGet(0);

        $mainItemDivs = $this->getMansoryItemDivs($node);

        foreach ($mainItemDivs as $mainItemDiv) {
            $card = $this->getCardCard2Div($mainItemDiv);
            $itemIds[] = $this->extractItemId($card);
        }
        return $itemIds;
    }

    protected function extractItemId(HtmlNode $cardcard2node)
    {
        foreach ($cardcard2node->getChildren() as $node) {
            if ($node instanceof HtmlNode && $node->getTag()->hasAttribute('data-itemid')) {
                return $node->getTag()->getAttribute('data-itemid')['value'];
            }
        }
        throw new \RuntimeException("Extracting itemid from cardnode failed. Maybe website changed?");
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

    public static function getLatestDropWeekParser()
    {
        return new static((new LatestDropDateResolver())->resolve());
    }

}