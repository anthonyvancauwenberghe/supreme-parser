<?php

namespace Supreme\Parser\Models\SupremeCommunity;

use Supreme\Parser\Abstracts\SupremeCommunityNameRouteTupple;
use Supreme\Parser\Parsers\DropTimesParser;

class SCDropTimesWeek extends SupremeCommunityNameRouteTupple
{
    protected $date;

    public function __construct(string $name, string $route, string $date)
    {
        parent::__construct($name, $route);
        $this->date = $date;
    }

    public function times()
    {
        $response = $this->client->get($this->route);
        $parser = new DropTimesParser($response);
        return $parser->parse();
    }

    public function getWeekName()
    {
        return $this->getName();
    }

    public function getWeekDate()
    {
        return $this->date;
    }
}
