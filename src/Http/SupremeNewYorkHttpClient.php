<?php


namespace Supreme\Parser\Http;


use GuzzleHttp\Client;
use Supreme\Parser\Parsers\LatestDroplistUrlParser;

class SupremeNewYorkHttpClient
{
    public $client;

    protected $baseUri = "https://www.supremenewyork.com";

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'headers' => [
                'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
                'accept-encoding' => 'gzip, deflate, br',
                'accept-language' => 'nl-NL,nl;q=0.9,en-US;q=0.8,en;q=0.7',
                'cache-control' => 'no-cache',
                'dnt' => 1,
                'pragma' => 1,
                'upgrade-insecure-requests' => 1,
                'referer' => 'https://www.supremenewyork.com/',
                'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.131 Safari/537.36'
            ]]);
    }

    public function getNews(int $page)
    {
        return $this->client->get("/news/page/" . $page);
    }
}
