<?php


namespace Supreme\Parser\Abstracts;


use Supreme\Parser\Http\SupremeCommunityHttpClient;

abstract class SupremeCommunityNameRouteTupple
{
    protected $name;
    protected $route;
    protected $client;

    /**
     * SeasonList constructor.
     * @param $name
     * @param $route
     */
    public function __construct(string $name, string $route)
    {
        $this->name = $name;
        $this->route = $route;
        $this->client = new SupremeCommunityHttpClient();
    }

    /**
     * @return mixed
     */
    protected function getRoute()
    {
        return $this->route;
    }

    /**
     * @return mixed
     */
    protected function getName()
    {
        return $this->name;
    }
}
