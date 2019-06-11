<?php


namespace Supreme\Parser\Http;


use GuzzleHttp\Client;
use Supreme\Parser\Parsers\LatestDroplistUrlParser;

class SupremeCommunityHttpClient
{
    public $client;

    protected $baseUri = "https://www.supremecommunity.com";

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
                'referer' => 'https://www.supremecommunity.com/season/spring-summer2019/droplists/',
                'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.131 Safari/537.36'
            ]]);
    }

    public function getLatestDroplistUrl(): string
    {
        $parser = new LatestDroplistUrlParser($this->getLatestSeasonDropListPage());
        return $parser->parse();
    }

    protected function getLatestSeasonDropListPage()
    {
        return $this->client->get('/season/latest/droplists/');
    }

    public function getLatestDroplistPage()
    {
        return $this->client->get($this->getLatestDroplistUrl());
    }

    public function getItem($id){
        return $this->client->get("/season/itemdetails/$id/");
    }

    public function getItemVote($id){
        return $this->client->get("/votes/items/$id/box/");
    }
}