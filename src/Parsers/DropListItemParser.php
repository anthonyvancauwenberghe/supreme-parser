<?php

namespace Supreme\Parser\Parsers;

use PHPHtmlParser\Dom\HtmlNode;
use Supreme\Parser\Abstracts\ResponseParser;

class DropListItemParser extends ResponseParser
{
    public function parse(): array
    {
        $title = $this->parseTitle();
        $caption = $this->parseDescription();
        $prices = $this->parsePrices();
        $colors = $this->parseColors();
        $image = $this->parseImage();

        return [
            "title" => $title,
            "caption" => $caption,
            "prices" => $prices,
            "colors" => $colors,
            "image" => $image
        ];
    }

    protected function parseTitle()
    {
        /** @var HtmlNode $node */
        $node = $this->dom->getElementsByClass("detail-title")[0];
        foreach ($node->getChildren() as $child) {
            return $child->text;
        }
    }

    protected function parseDescription()
    {
        /** @var HtmlNode $node */
        $node = $this->dom->getElementsByClass("detail-desc")[0];
        foreach ($node->getChildren() as $child) {
            return $child->text;
        }
    }

    protected function parsePrices()
    {
        /** @var HtmlNode $node */
        $prices = [];
        $node = $this->dom->getElementsByClass("itemdetails-centered")[0];
        if ($node !== null) {
            foreach ($node->getChildren() as $child) {
                if ($child instanceof HtmlNode && $child->hasChildren()) {
                    foreach ($child->getChildren() as $textNode) {
                        $prices[] = $textNode->text;
                    }

                }
            }
        }
        return $prices;
    }

    protected function parseColors()
    {
        /** @var HtmlNode $node */
        $colors = [];
        $node = $this->dom->getElementsByClass("itemdetails-centered")[1];
        if ($node !== null) {
            foreach ($node->getChildren() as $child) {
                if ($child instanceof HtmlNode && $child->hasChildren()) {
                    foreach ($child->getChildren() as $textNode) {
                        $colors[] = $textNode->text;
                    }

                }
            }
        }
        return $colors;
    }

    protected function parseImage()
    {
        /** @var HtmlNode $imageNode */
        $imageNode = $this->dom->getElementsByTag('img')[0];
        $route = $imageNode->getTag()->getAttribute('src')['value'];
        return "https://supremecommunity.com" . $route;
    }
}