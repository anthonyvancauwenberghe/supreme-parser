<?php

namespace Supreme\Parser\Parsers;

use PHPHtmlParser\Dom\HtmlNode;
use Supreme\Parser\Abstracts\ResponseParser;
use Supreme\Parser\Traversers\RecursiveNodeWalker;
use Supreme\Parser\Traversers\RecursiveNodeWalkerTextFinder;

class DropTimesParser extends ResponseParser
{
    public function parse(): array
    {
        $walker = new RecursiveNodeWalker($this->dom->root, 'class', "sellout-item", false);
        $itemNodes = $walker->traverse();

        return collect($itemNodes)->map(function (HtmlNode $node) {
            return [
                "name" => $this->parseName($node),
                "style" => $this->parseStyle($node),
                "size" => $this->parseSize($node),
                "image" => $this->parseImage($node),
                "time" => $this->parseTime($node)
            ];
        })->toArray();
    }

    public function findBasedOnSelloutAttribute(HtmlNode $node, string $name)
    {
        $walker = new RecursiveNodeWalker($node, 'class', "sellout-" . $name, false);
        $node = $walker->traverseTillFirst();

        $textFinder = new RecursiveNodeWalkerTextFinder($node);
        $texts = $textFinder->traverse();

        $output = $texts[0] ?? null;

        if ($output !== null)
            $output = ltrim($this->strip_tags_content(htmlspecialchars_decode($output, ENT_QUOTES)));

        return $output;
    }

    public function parseName(HtmlNode $node)
    {
        return $this->findBasedOnSelloutAttribute($node, "name");
    }

    public function parseStyle(HtmlNode $node)
    {
        return explode('-', $this->findBasedOnSelloutAttribute($node, "colorway"))[0] ?? "N/A";
    }

    public function parseSize(HtmlNode $node)
    {
        return explode('-', $this->findBasedOnSelloutAttribute($node, "colorway"))[1] ?? "N/A";
    }

    public function parseImage(HtmlNode $node)
    {
        $walker = new RecursiveNodeWalker($node, 'class', "sellout-image", false);
        $node = $walker->traverseTillFirst();
        $walker = new RecursiveNodeWalker($node, 'src', "/", false);
        $node = $walker->traverseTillFirst();
        $image = $node->tag->getAttribute('data-src')['value'];
        return $this->filterImageUrl($image);
    }

    public function parseTime(HtmlNode $node)
    {
        return $this->findBasedOnSelloutAttribute($node, "times");
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


    protected function filterImageUrl($url)
    {
        $images = str_replace('//', 'https://', $url);
        $images = str_replace('[', '', $images);
        $images = str_replace(']', '', $images);
        $images = htmlspecialchars_decode($images, ENT_QUOTES);
        $images = str_replace('"', '', $images);
        return $images;
    }

}
