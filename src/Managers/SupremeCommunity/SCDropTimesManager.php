<?php

namespace Supreme\Parser\Managers\SupremeCommunity;

use Illuminate\Support\Facades\Storage;
use Supreme\Parser\Http\SupremeCommunityHttpClient;
use Supreme\Parser\Models\SupremeCommunity\SCDropTimesSeason;
use Supreme\Parser\Models\SupremeCommunity\SCDropTimesWeek;
use Supreme\Parser\Parsers\DropTimesSeasonListParser;

class SCDropTimesManager
{
    protected $region;

    protected $client;

    /**
     * SCDropTimes constructor.
     * @param $region
     */
    public function __construct(string $region)
    {
        $this->region = $region;
        $this->client = new SupremeCommunityHttpClient();
    }

    /**
     * @return \Illuminate\Support\Collection | SCDropTimesSeason[]
     */
    public function seasons()
    {
        $response = $this->client->getLatestSeasonDropTimes($this->region);
        $parser = new DropTimesSeasonListParser($response);
        return collect($parser->parse())->map(function (array $season) {
            return new SCDropTimesSeason($season['name'], $season['route']);
        });
    }

    public function all()
    {
        return $this->seasons()->map(function (SCDropTimesSeason $season) {
            sleep(2);
            echo "parsing season: ".$season->getSeasonName() . PHP_EOL;
            return [
                "season" => $season->getSeasonName(),
                "weeks" => $season->weeks()->map(function (SCDropTimesWeek $week) {
                    sleep(3);
                    echo "parsing week: ".$week->getWeekName() . PHP_EOL;
                    return [
                        "week" => $week->getWeekName(),
                        "date" => $week->getWeekDate(),
                        "times" => $week->times()
                    ];
                })
            ];
        })->toArray();


    }

}






