<?php

namespace Supreme\Parser\Parsers;

use PHPHtmlParser\Dom\HtmlNode;
use Supreme\Parser\Abstracts\ResponseParser;
use Supreme\Parser\Traversers\RecursiveNodeWalker;
use Supreme\Parser\Traversers\RecursiveNodeWalkerTextFinder;

class DropTimesWeekParser extends ResponseParser
{
    public function parse(): array
    {
        $walker = new RecursiveNodeWalker($this->dom->root, 'id', "box-", false);

        $weeks = collect($walker->traverse())->map(function (HtmlNode $node) {
            return [
                "week" => $this->parseWeekName($node),
                "date" => $this->parseDate($node),
                "route" => $this->parseWeekRoute($node)
            ];
        })->toArray();

        $weeks = collect($weeks)->filter(function (array $data) {
            return strtolower($data['week']??'') !== 'latest';
        })->values()->toArray();

        return $weeks;
    }

    protected function parseWeekRoute(HtmlNode $node)
    {
        $walker = new RecursiveNodeWalker($node, 'href', "/season", false);
        $node = $walker->traverseTillFirst();

        return $node->tag->getAttribute('href')['value'];
    }

    protected function parseWeekName(HtmlNode $node)
    {
        $walker = new RecursiveNodeWalkerTextFinder($node);
        $texts = $walker->traverse();
        return $texts[0];
    }

    protected function parseDate(HtmlNode $node)
    {
        $walker = new RecursiveNodeWalkerTextFinder($node);
        $texts = $walker->traverse();
        return $texts[1] ?? null;
    }
}
