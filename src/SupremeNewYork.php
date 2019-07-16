<?php


namespace Supreme\Parser;

use GuzzleHttp\Exception\ClientException;
use Supreme\Parser\Http\SupremeCommunityHttpClient;
use Supreme\Parser\Http\SupremeNewYorkHttpClient;
use Supreme\Parser\Parsers\DropListItemIdsParser;
use Supreme\Parser\Parsers\DropListItemParser;
use Supreme\Parser\Parsers\SupremeNewsParser;

class SupremeNewYork
{
    public $client;

    public function __construct()
    {
        $this->client = new SupremeNewYorkHttpClient();
    }

    public function parseNews()
    {
        $news = [];
        for($i=1; $i<30; $i++){
            $response = $this->client->getNews($i);
            $parsed =(new SupremeNewsParser($response))->parse();
            $news = array_merge($news,$parsed);
        }
        return $news;
    }
}
