<?php

namespace Supreme\Parser\Parsers;

use PHPHtmlParser\Dom\HtmlNode;
use Supreme\Parser\Abstracts\ResponseParser;
use Supreme\Parser\Traversers\RecursiveNodeWalker;

class DropTimesSeasonListParser extends ResponseParser
{
    public function parse(): array
    {
        $nodes = $this->filterHtmlNodesOnly($this->dom->getElementById('anyPicker')->getChildren());

        $seasons = [];
        foreach ($nodes as $optionNode) {
            $seasons[] = [
                "name" => $this->parseSeasonName($optionNode),
                "route" => $this->parseSeasonRoute($optionNode),
            ];
        }
        return $seasons;
    }

    protected function parseSeasonRoute(HtmlNode $node)
    {
        return $node->tag->getAttribute('value')['value'];
    }

    protected function parseSeasonName(HtmlNode $node)
    {
        return $node->getChildren()[0]->text;
    }


}
