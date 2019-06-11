<?php

namespace Supreme\Parser\Abstracts;

use PHPHtmlParser\Dom;
use Psr\Http\Message\ResponseInterface;

abstract class ResponseParser
{
    protected $dom;

    public function __construct(ResponseInterface $response)
    {
        $this->dom = new Dom();
        $this->initDom($response);
    }

    private function initDom(ResponseInterface $response)
    {
        $this->dom->loadStr($response->getBody());
    }

    public abstract function parse();
}