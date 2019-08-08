<?php


namespace Supreme\Parser\Models\SupremeCommunity;


use Supreme\Parser\Abstracts\SupremeCommunityNameRouteTupple;
use Supreme\Parser\Parsers\DropTimesWeekParser;

class SCDropTimesSeason extends SupremeCommunityNameRouteTupple
{
    /**
     * @return \Illuminate\Support\Collection | SCDropTimesWeek[]
     */
    public function weeks()
    {
        $response = $this->client->get($this->route);
        $parser = new DropTimesWeekParser($response);
        return collect($parser->parse())->map(function (array $data) {
            return new SCDropTimesWeek($data['week'], $data['route'], $data['date']);
        });
    }

    public function getSeasonName()
    {
        return $this->getName();
    }
}
