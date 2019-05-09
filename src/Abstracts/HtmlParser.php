<?php

namespace Supreme\Parser\Abstracts;

use GuzzleHttp\Client;

abstract class HtmlParser extends ResponseParser
{
    protected $baseUrl;

    public function __construct(string $route)
    {
        parent::__construct($this->getResponse($route));
    }

    private function getResponse(string $route)
    {
        $client = new Client();
        return $client->request('GET', $this->baseUrl . $route);
    }
}