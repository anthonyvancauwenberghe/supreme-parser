<?php

namespace Supreme\Parser;

use Supreme\Parser\Abstracts\HtmlParser;

class SupremeLookbookProductRoutesParser extends HtmlParser
{
    protected $baseUrl = "https://www.supremenewyork.com";

    public function parse(): array
    {
        $urls = [];
        foreach ($this->dom->getElementById("container") as $article) {
            $urls[] = $this->filterUrl($article->firstChild()->firstChild()->getAttribute("href"));
        };
        $urls = array_unique($urls);
        return $urls;
    }

    private function filterUrl($url)
    {
        $lastCharacters = substr($url, -3);

        if (($pos = strpos($lastCharacters, '-')) !== false) {
            return substr($url, 0, $pos - 3);
        }
        return $url;
    }
}