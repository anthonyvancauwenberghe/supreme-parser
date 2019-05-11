<?php

namespace Supreme\Parser;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class SupremeCommunityLatestDroplistParser
{
    protected $debug;

    protected $items;

    protected $client;

    protected $delay;

    public function __construct(int $delayBetweenRequests = 1, bool $debug = false)
    {
        $this->debug = $debug;
        $this->delay = $delayBetweenRequests;
    }

    public function parse(): array
    {
        if ($this->delay <= 0)
            $this->requestAsync();
        else
            $this->request();
        return $this->items;
    }

    protected function requestAsync()
    {
        $pool = new Pool(new Client(), $this->buildRequests(), [
            'concurrency' => 1,
            'fulfilled' => function (Response $response, $index) {
                $parser = new SupremeCommunityDropListItemParser($response);
                $this->items[] = $parser->parse();

                if ($this->debug)
                    echo "Successfully parsed: ' $index \n";
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
    }

    protected function request()
    {
        $http = new Client();
        foreach ($this->buildRequests() as $index => $request) {
            try {
                sleep($this->delay);
                $response = $http->send($request);
                $parser = new SupremeCommunityDropListItemParser($response);
                $this->items[] = $parser->parse();
                if ($this->debug)
                    echo "Successfully parsed: ' $index \n";
            } catch (ClientException $exception) {
                if ($this->debug) {
                    if ($exception->getCode() == 404)
                        echo "failed to request: $index  -  404 . \n";
                    else
                        echo "failed to request: $index  -  " . $exception->getCode() . " - " . $exception->getMessage() . " \n";
                }
            }

        }
    }

    protected function buildRequests()
    {
        $parser = SupremeCommunityDropListItemIdsParser::getLatestDropWeekParser();
        $ids = $parser->parse();

        $requests = [];
        foreach ($ids as $id) {
            $requests[$id] = new Request("GET", "https://www.supremecommunity.com/season/itemdetails/$id/",
                [
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
        return $requests;
    }

}
