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
}
