<?php

namespace Supreme\Parser\Abstracts;

use PHPHtmlParser\Dom;
use Psr\Http\Message\ResponseInterface;

abstract class ResponseParser
{
    protected $dom;
    protected $html;

    public function __construct(ResponseInterface $response)
    {
        $this->dom = new Dom();
        $this->initDom($this->response = $response);
    }

    private function initDom(ResponseInterface $response)
    {
        $this->html = $response->getBody()->__toString();
        $this->dom->loadStr($response->getBody());
    }

    public abstract function parse();

    public function filterHtmlNodesOnly(array $nodes)
    {
        $output = [];

        foreach ($nodes as $node) {
            if ($node instanceof Dom\HtmlNode)
                $output[] = $node;
        }
        return $output;
    }
}
