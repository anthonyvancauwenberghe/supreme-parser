<?php

namespace Supreme\Parser\Abstracts;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPHtmlParser\Dom;

abstract class ResponseParser
{
    protected $dom;

    public function __construct(Response $response)
    {
        $this->dom = new Dom();
        $this->initDom($response);
    }

    private function initDom(Response $response)
    {
        $this->dom->loadStr($response->getBody());
    }

    public abstract function parse();
}