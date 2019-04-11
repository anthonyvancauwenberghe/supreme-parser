<?php

namespace Supreme\Parser\Abstracts;

use GuzzleHttp\Client;
use PHPHtmlParser\Dom;

abstract class SupremeHtmlParser
{
    protected $baseUrl = "https://www.supremenewyork.com";

    protected $route;

    protected $dom;

    public function __construct(?string $route = null)
    {
        $this->dom = new Dom();
        $this->loadHtml($route ?? $this->route);
    }

    private function loadHtml($route)
    {
        $client = new Client();
        $response = $client->request('GET', $this->baseUrl.$route);
        $this->dom->loadStr($response->getBody());
    }

    public abstract function parse();
}