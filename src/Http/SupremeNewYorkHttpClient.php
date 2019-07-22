<?php


namespace Supreme\Parser\Http;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Supreme\Parser\Parsers\LatestDroplistUrlParser;
use Supreme\Parser\ProductParser;

class SupremeNewYorkHttpClient
{
    public $client;

    public $debug = false;

    protected $baseUri = "https://www.supremenewyork.com";

    public function __construct()
    {
        $this->client = new Client([
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

    public function setBaseUri(string $uri)
    {
        $this->baseUri = $uri;
    }

    public function setDebugMode(bool $debug)
    {
        $this->debug = $debug;
    }


    public function getNews(int $page)
    {
        return $this->client->get($this->baseUri . "/news/page/" . $page);
    }

    public function getStock()
    {
        return $this->client->get($this->baseUri . "/shop.json");
    }

    public function getMobileStock()
    {
        return $this->client->get($this->baseUri . "/mobile_stock.json");
    }

    public function getItem($id)
    {
        return $this->client->get($this->baseUri . "/shop/$id.json");
    }

    public function getItems(array $ids, $concurrency = 4)
    {
        $items = [];
        $requests = [];

        foreach ($ids as $id) {
            $requests[$id] = new Request("GET", $this->baseUri . "/shop/$id.json");
        }

            $pool = new Pool($this->client, $requests, [
                'concurrency' => $concurrency,
                'fulfilled' => function (Response $response, $id) use (&$items) {
                    $items[$id] = $response;
                },
                'rejected' => function (RequestException $reason, $index) {
                    if ($this->debug) {
                        if ($reason->getCode() == 404)
                            echo "failed to request: $index  -  404 . \n";
                        else
                            echo "failed to request: $index  -  $reason . \n";
                    }

                },
            ]);

            // Initiate the transfers and create a promise
            $promise = $pool->promise();

            // Force the pool of requests to complete.
            $promise->wait();

        return $items;
    }
}
