<?php

namespace Supreme\Parser\Parsers;

use PHPHtmlParser\Dom\HtmlNode;
use Psr\Http\Message\ResponseInterface;
use Supreme\Parser\Abstracts\ResponseParser;
use Supreme\Parser\Traversers\RecursiveNodeWalker;

class DropListItemParser extends ResponseParser
{
    protected $category;

    /**
     * DropListItemParser constructor.
     * @param $category
     */
    public function __construct(ResponseInterface $response, string $category = 'Unknown')
    {
        parent::__construct($response);
        $this->category = $category;
    }

    public function parse(): array
    {
        $title = $this->parseTitle();
        $caption = $this->parseDescription();
        $prices = $this->parsePrices();
        $colors = $this->parseColors();
        $image = $this->parseImage();

        return [
            "title" => $title,
            "category" => $this->category,
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
        $traverser = new RecursiveNodeWalker($this->dom->root, 'class', 'price-label', false);
        return $traverser->traverse(function (?HtmlNode $node) {

            if ($node === null)
                throw new \RuntimeException("failed to parse prices from droplist item");

            $price = ($node->innerHtml() ?? $node->text());

            $encoded = ltrim(htmlentities($price));
            if (in_array($start = $encoded[0], ['$', '&'])) {
                return ltrim($this->strip_tags_content(htmlspecialchars_decode($price, ENT_QUOTES)));
            }

        });
    }

    protected function parseColors()
    {
        $traverser = new RecursiveNodeWalker($this->dom->root, 'class', 'price-label', false);
        return $traverser->traverse(function (?HtmlNode $node) {
            if ($node === null)
                throw new \RuntimeException("failed to parse prices from droplist item");

            $color = ($node->innerHtml() ?? $node->text());

            $encoded = ltrim(htmlentities($color));
            if (!in_array($start = $encoded[0], ['$', '&'])) {
                return ltrim($this->strip_tags_content(htmlspecialchars_decode($color, ENT_QUOTES)));
            }
        });
    }

    protected function parseCategory()
    {

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
