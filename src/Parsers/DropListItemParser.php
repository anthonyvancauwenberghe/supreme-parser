<?php

namespace Supreme\Parser\Parsers;

use PHPHtmlParser\Dom\HtmlNode;
use Psr\Http\Message\ResponseInterface;
use Supreme\Parser\Abstracts\ResponseParser;
use Supreme\Parser\Http\SupremeCommunityHttpClient;
use Supreme\Parser\Traversers\RecursiveNodeWalker;
use Supreme\Parser\Traversers\RecursiveNodeWalkerTextFinder;

class DropListItemParser extends ResponseParser
{
    protected $category;
    protected $id;

    public function __construct(ResponseInterface $response, string $category = 'Unknown', ?int $id = null)
    {
        parent::__construct($response);
        $this->category = $category;
        $this->id = $id;
    }

    public function parse(): array
    {
        $title = $this->parseTitle();
        $caption = $this->parseDescription();
        $prices = $this->parsePrices();
        $colors = $this->parseColors();
        $image = $this->parseImage();
        $release = $this->parseRelease();

        return [
            "id" => $this->id,
            "title" => $title,
            "category" => $this->category,
            "caption" => $caption,
            "prices" => $prices,
            "colors" => $colors,
            "image" => $image,
            "images" => $this->parseImages(),
            "release" => $release
        ];
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

    protected function parseRelease(): ?string
    {
        $traverser = new RecursiveNodeWalker($this->dom->root, 'class', 'details-release-small');
        $node = $traverser->traverseTillFirst();
        $finder = new RecursiveNodeWalkerTextFinder($node);
        $texts = $finder->traverse();
        return $texts[1] ?? null;
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

    protected function parseImages()
    {
        $traverser = new RecursiveNodeWalker($this->dom->root, 'data-image-hq');
        $nodes = $traverser->traverse();
        return collect($nodes)
            ->map(function (HtmlNode $node) {
                return $node->getTag()->getAttribute('data-image-hq')['value'];
            })
            ->map(function (string $route) {
                return 'https://supremecommunity.com' . $route;
            })
            ->toArray();
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
