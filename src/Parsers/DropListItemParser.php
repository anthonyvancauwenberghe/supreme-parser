<?php

namespace Supreme\Parser\Parsers;

use PHPHtmlParser\Dom\HtmlNode;
use Supreme\Parser\Abstracts\ResponseParser;
use Supreme\Parser\Traversers\RecursiveNodeWalker;

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

    public function stringContains(string $needle, string $haystack)
    {
        return strpos($haystack, $needle) !== false;
    }

    public function recursiveWalkNode(?HtmlNode $node, string $classNameToFind, &$savedNode = null): ?HtmlNode
    {
        if ($savedNode !== null)
            return $savedNode;

        if ($node === null)
            return null;

        if ($node->getTag()->hasAttribute('class') && $this->stringContains($classNameToFind, $id = $node->getTag()->getAttribute('class')['value'])) {
            $savedNode = $node;
        }

        if ($savedNode !== null)
            return $savedNode;

        foreach ($node->getChildren() as $child) {
            if ($child instanceof HtmlNode)
                $this->recursiveWalkNode($child, $classNameToFind, $savedNode);
        }

        return $savedNode;
    }

    public function recursiveWalkToString(string $className)
    {
        $traverser = new RecursiveNodeWalker($this->dom->root, 'class', $className);
        return $traverser->traverseTillFirst(function (?HtmlNode $node) {
            if ($node === null)
                throw new \RuntimeException("failed to parse text from droplist item");
            return $this->strip_tags_content(htmlspecialchars_decode($node->innerHtml() ?? $node->text(), ENT_QUOTES));
        });
    }

    protected function parseTitle()
    {
        return $this->recursiveWalkToString("detail-title");
    }

    protected function parseDescription()
    {
        return $this->recursiveWalkToString("detail-desc");
    }

    protected function parsePrices()
    {
        $prices = [];
        $node = $this->recursiveWalkNode($this->dom->root, "itemdetails-centered");
        if ($node !== null) {
            foreach ($node->getChildren() as $child) {
                if ($child instanceof HtmlNode && $child->hasChildren()) {
                    foreach ($child->getChildren() as $textNode) {
                        $prices[] = $textNode->text;
                    }

                }
            }
        } else {
            throw new \RuntimeException("failed to parse prices from droplist item");
        }
        return $prices;
    }

    protected function parseColors()
    {
        /** @var HtmlNode $node */
        $colors = [];
        $node = $this->recursiveWalkNode($this->dom->root, "itemdetails-centered");
        if ($node !== null) {
            foreach ($node->getChildren() as $child) {
                if ($child instanceof HtmlNode && $child->hasChildren()) {
                    foreach ($child->getChildren() as $textNode) {
                        $colors[] = htmlspecialchars_decode($textNode->text, ENT_QUOTES);
                    }

                }
            }
        } else {
            throw new \RuntimeException("failed to parse colors from droplist item");
        }
        return $colors;
    }

    protected function parseImage()
    {
        /** @var HtmlNode $imageNode */
        $imageNode = $this->dom->getElementsByTag('img')[0] ?? null;

        if ($imageNode === null)
            throw new \RuntimeException("failed parsing image tag from droplist item");

        $route = $imageNode->getTag()->getAttribute('src')['value'];
        return "https://supremecommunity.com" . $route;
    }

    protected function strip_tags_content($text, $tags = '', $invert = FALSE)
    {

        preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
        $tags = array_unique($tags[1]);

        if (is_array($tags) AND count($tags) > 0) {
            if ($invert == FALSE) {
                return preg_replace('@<(?!(?:' . implode('|', $tags) . ')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
            } else {
                return preg_replace('@<(' . implode('|', $tags) . ')\b.*?>.*?</\1>@si', '', $text);
            }
        } elseif ($invert == FALSE) {
            return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
        }
        return $text;
    }
}
