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
        return $client->request('GET', $this->baseUrl . $route, [
            'headers' => [
                'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
                'accept-encoding' => 'gzip, deflate, br',
                'accept-language' => 'nl-NL,nl;q=0.9,en-US;q=0.8,en;q=0.7',
                'cache-control' => 'no-cache',
                'dnt' => 1,
                'pragma' => 1,
                'upgrade-insecure-requests' => 1,
                'referer' => 'https://www.supremecommunity.com/season/spring-summer2019/droplists/',
                'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.131 Safari/537.36'
            ]
        ]);
    }
}
