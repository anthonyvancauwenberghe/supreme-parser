<?php

namespace Supreme\Parser\Parsers;

use PHPHtmlParser\Dom\HtmlNode;
use Psr\Http\Message\ResponseInterface;
use Supreme\Parser\Abstracts\ResponseParser;
use Supreme\Parser\Http\SupremeCommunityHttpClient;
use Supreme\Parser\Traversers\RecursiveNodeWalker;
use Supreme\Parser\Traversers\RecursiveNodeWalkerTextFinder;
use function foo\func;
use function PHPUnit\Framework\isEmpty;

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
        $votesUp = $this->parseVotesUp();
        $votesDown = $this->parseVotesDown();
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
            "votes_up" => $votesUp,
            "votes_down" => $votesDown,
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
        $traverser = new RecursiveNodeWalker($this->dom->root, 'class', 'itemdetails-labels', false);
        $nodes = $traverser->traverse();

        $labels = collect($nodes)->map(function ($node) {
            if ($node instanceof HtmlNode) {
                $pricelabels = (new RecursiveNodeWalker($node, 'class', 'price-label', false))->traverse();
                $data = [];
                foreach ($pricelabels as $aNode) {
                    $data[] = $aNode->innerHtml() ?? $aNode->text();
                }
                return $data;
            }
            return null;
        })->reject(fn($value) => $value === null || empty($value))->toArray();

        return $labels[0];
        // return collect($prices[0])->transform(fn($price) => trim($price))->toArray();
    }

    protected function parseColors()
    {
        $traverser = new RecursiveNodeWalker($this->dom->root, 'class', 'itemdetails-labels', false);
        $nodes = $traverser->traverse();

        $labels = collect($nodes)->map(function ($node) {
            if ($node instanceof HtmlNode) {
                $pricelabels = (new RecursiveNodeWalker($node, 'class', 'price-label', false))->traverse();
                $data = [];
                foreach ($pricelabels as $aNode) {
                    $data[] = $aNode->innerHtml() ?? $aNode->text();
                }
                return $data;
            }
            return null;
        })->reject(fn($value) => $value === null || empty($value))->toArray();

        return $labels[1];
    }

    protected function parseVotesUp()
    {
        $traverser = new RecursiveNodeWalker($this->dom->root, 'class', 'upvotes', false);
        $data = $traverser->traverse(function (?HtmlNode $node) {
            if ($node === null)
                return null;

            return ((int)($node->innerHtml() ?? $node->text())) ?? null;
        });

        return $data[0];
    }

    protected function parseVotesDown()
    {
        $traverser = new RecursiveNodeWalker($this->dom->root, 'class', 'downvotes', false);
        $data = $traverser->traverse(function (?HtmlNode $node) {
            if ($node === null)
                return null;

            return ((int)($node->innerHtml() ?? $node->text())) ?? null;
        });

        return $data[0];
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

        if (is_array($tags) and count($tags) > 0) {
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
