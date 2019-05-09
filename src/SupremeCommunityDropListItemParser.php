<?php

namespace Supreme\Parser;

use GuzzleHttp\Psr7\Response;
use PHPHtmlParser\Dom\HtmlNode;
use Supreme\Parser\Abstracts\ResponseParser;

class SupremeCommunityDropListItemParser extends ResponseParser
{
    public function __construct(Response $response)
    {
        parent::__construct($response);
    }

    public function parse(): array
    {
        $title = $this->parseTitle();
        $caption = $this->parseDescription();
        $upvotes = $this->parseUpVotes();
        $downvotes = $this->parseDownVotes();
        $prices = $this->parsePrices();
        $colors = $this->parseColors();
        $image = $this->parseImage();

        return [
            "title" => $title,
            "caption" => $caption,
            "upvotes" => (int)$upvotes,
            "downvotes" => (int)$downvotes,
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

    protected function parseUpVotes()
    {
        /** @var HtmlNode $node */
        $node = $this->dom->getElementsByClass("upvotes")[0];
        foreach ($node->getChildren() as $child) {
            return $child->text;
        }
    }

    protected function parseDownVotes()
    {
        /** @var HtmlNode $node */
        $node = $this->dom->getElementsByClass("downvotes")[0];
        foreach ($node->getChildren() as $child) {
            return $child->text;
        }
    }

    protected function parsePrices()
    {
        /** @var HtmlNode $node */
        $prices = [];
        $node = $this->dom->getElementsByClass("itemdetails-centered")[0];
        foreach ($node->getChildren() as $child) {
            if ($child instanceof HtmlNode) {
                foreach ($child->getChildren() as $textNode) {
                    $prices[] = $textNode->text;
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
        foreach ($node->getChildren() as $child) {
            if ($child instanceof HtmlNode) {
                foreach ($child->getChildren() as $textNode) {
                    $colors[] = $textNode->text;
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