<?php

namespace Supreme\Parser\Parsers;

use PHPHtmlParser\Dom\HtmlNode;
use Supreme\Parser\Abstracts\ResponseParser;
use Supreme\Parser\Traversers\RecursiveNodeWalker;

class SupremeNewsParser extends ResponseParser
{
    public function parse(): array
    {
        $news = [];

        $traverser = new RecursiveNodeWalker($this->dom->root, 'class', 'news_container');
        $nodes = $traverser->traverse();
        foreach ($nodes as $node) {
            $news[] = [
                "title" => $this->extractTitle($node),
                "date" => $this->extractDate($node),
                "images" => $this->extractImages($node),
                "article" => $this->extractArticle($node)
            ];
        }
        return $news;
    }

    public function extractImages(HtmlNode $node)
    {
        $traverser = new RecursiveNodeWalker($node, null, null);

        $nodes = $traverser->traverse();

        foreach ($nodes as $node) {
            if ($node->getTag()->hasAttribute('data-image-urls')) {
                $images = $node->getTag()->getAttribute('data-image-urls')['value'];
                $images = str_replace('//', 'https://', $images);
                $images = str_replace('[', '', $images);
                $images = str_replace(']', '', $images);
                $images = htmlspecialchars_decode($images, ENT_QUOTES);
                $images = str_replace('"', '', $images);
                return explode(',', $images);
            }
        }

            return [];
    }

    public function extractTitle(HtmlNode $node)
    {
        $traverser = new RecursiveNodeWalker($node, null, null);
        $nodes = $traverser->traverse();

        foreach ($nodes as $node) {
            if ($node->getTag()->name() === 'h2')
                return $node->text;
        }
    }

    public function extractDate(HtmlNode $node)
    {
        $traverser = new RecursiveNodeWalker($node, null, null);
        $nodes = $traverser->traverse();

        foreach ($nodes as $node) {
            if ($node->getTag()->name() === 'time')
                return $node->text;
        }
    }

    public function extractArticle(HtmlNode $node)
    {
        $traverser = new RecursiveNodeWalker($node, 'class', 'blurb');
        return $traverser->traverseTillFirst(function (?HtmlNode $node) {
            if ($node === null)
                return;
            $article = $node->text;
            $article = str_replace('//', 'https://', $article);
            $article = str_replace('[', '', $article);
            $article = str_replace(']', '', $article);
            $article = htmlspecialchars_decode($article, ENT_QUOTES);
            return str_replace('"', '', $article);
        });
    }


}
