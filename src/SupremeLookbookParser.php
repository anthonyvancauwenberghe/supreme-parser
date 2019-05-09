<?php

namespace Supreme\Parser;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class SupremeLookbookParser
{
    protected $route;

    protected $debug;

    protected $products = [];

    public $transformer;

    public function __construct(string $route, bool $debug = false)
    {
        $this->route = $route;
        $this->debug = $debug;
    }

    protected function getProductUrls()
    {
        return (new SupremeLookbookProductRoutesParser($this->route))->parse();
    }

    public function parse()
    {
        $this->execute();
        return $this->products;
    }

    protected function execute()
    {
        $pool = new Pool(new Client(), $this->buildRequests(), [
            'concurrency' => 10,
            'fulfilled' => function (Response $response, $index) {
                $parser = new ProductParser($response);
                $product = $this->transform($parser->parse());
                if (!empty($product))
                    $this->products[] = $this->transform($parser->parse());

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

    protected function buildRequests()
    {
        $requests = [];
        foreach ($this->getProductUrls() as $url) {
            $requests[$url] = new Request("GET", "https://www.supremenewyork.com" . $url);
        }
        return $requests;
    }

    public function setTransformer(callable $transformer)
    {
        return $this->transformer = $transformer;
    }

    protected function transform($product): array
    {
        if ($this->transformer === null || !is_callable($this->transformer))
            return $product ?? [];
        $callable = $this->transformer;
        return $callable($product);
    }

}