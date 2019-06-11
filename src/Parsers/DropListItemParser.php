<?php

namespace Supreme\Parser\Parsers;

use PHPHtmlParser\Dom\HtmlNode;
use Supreme\Parser\Abstracts\ResponseParser;

class DropListItemParser extends ResponseParser
{
    public function parse(): array
    {
        $title = $this->strip_tags_content($this->parseTitle());
        $caption = $this->strip_tags_content($this->parseDescription());
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
            return htmlspecialchars_decode($child->text, ENT_QUOTES);
        }
    }

    protected function parseDescription()
    {
        /** @var HtmlNode $node */
        $node = $this->dom->getElementsByClass("detail-desc")[0];
        foreach ($node->getChildren() as $child) {
            return htmlspecialchars_decode($child->text, ENT_QUOTES);
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
                        $colors[] = htmlspecialchars_decode($textNode->text, ENT_QUOTES);
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

    protected function strip_tags_content($text, $tags = '', $invert = FALSE)
    {

        preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
        $tags = array_unique($tags[1]);

        if(is_array($tags) AND count($tags) > 0)
        {
            if($invert == FALSE)
            {
                return preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
            }
            else
            {
                return preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);
            }
        }
        elseif($invert == FALSE)
        {
            return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
        }
        return $text;
    }
}