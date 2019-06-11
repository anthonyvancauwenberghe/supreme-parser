<?php

namespace Supreme\Parser\Parsers;

use GuzzleHttp\Psr7\Response;
use PHPHtmlParser\Dom\HtmlNode;
use Supreme\Parser\Abstracts\ResponseParser;

class DropListItemVoteParser extends ResponseParser
{
    public function parse(): array
    {
        $upvotes = intval($this->parseUpVotes());
        $downvotes = 100 - $upvotes;
        return [
            "upvotes" => (int)$upvotes,
            "downvotes" => (int)$downvotes,
        ];
    }

    protected function parseUpVotePercentage()
    {
        /** @var HtmlNode $node */
        $node = $this->dom->getElementsByClass('droplist-vote-bar'); //getElementsByClass("droplist-vote-bar")[0];

        if ($node === null) {
            return "0";
        }

        if ($node->getTag()->hasAttribute('style')) {
            $style = $node->getTag()->getAttribute('style');
            $style = $style["value"];
            $styles = explode(';', $style);
            if (!empty($styles)) {
                foreach ($styles as $style) {
                    if (strpos($style, 'width:') !== false) {
                        $string = str_replace('width:', '', $style);
                        $string = str_replace(' ', '', $string);
                        return str_replace('%', '', $string);
                    }
                }
            }
        }
        throw new \RuntimeException("Extracting votes failed. Maybe website changed?");
    }

    protected function parseUpVotes()
    {
        /** @var HtmlNode $node */
        $node = $this->dom->getElementsByClass('droplist-vote-bar'); //getElementsByClass("droplist-vote-bar")[0];

        throw new \RuntimeException("Extracting votes failed. Maybe website changed?");
    }
}