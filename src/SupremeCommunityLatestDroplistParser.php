<?php

namespace Supreme\Parser;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class SupremeCommunityLatestDroplistParser
{
    protected $debug;

    protected $items;

    public function __construct(bool $debug = false)
    {
        $this->debug = $debug;
    }

    public function parse(): array
    {

        $this->execute();
        return $this->items;
    }

    protected function execute()
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

    protected function buildRequests(){
        $parser = SupremeCommunityDropListItemIdsParser::getLatestDropWeekParser();
        $ids = $parser->parse();

        $requests = [];
        foreach ($ids as $id) {
            $requests[$id] = new Request("GET", "https://supremecommunity.com/season/itemdetails/$id/");
        }
        return $requests;
    }

}