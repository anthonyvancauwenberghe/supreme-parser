<?php


namespace Supreme\Parser;

use GuzzleHttp\Exception\ClientException;
use Supreme\Parser\Http\SupremeCommunityHttpClient;
use Supreme\Parser\Parsers\DropListItemIdsParser;
use Supreme\Parser\Parsers\DropListItemParser;
use Supreme\Parser\Parsers\SCSeasonListPicker;
use Supreme\Parser\Parsers\SeasonListItemIdsParser;

class SupremeCommunity
{
    protected $debug;

    public $supremeHttp;

    protected $delay;

    public function __construct(int $delayBetweenRequests = 2, bool $debug = false)
    {
        $this->debug = $debug;
        $this->delay = $delayBetweenRequests;
        $this->supremeHttp = new SupremeCommunityHttpClient();
    }

    public function getItemIds(?string $season = null, ?string $date = null)
    {
        $dom = (($season === null || $date === null) ? $this->supremeHttp->getLatestDroplistPage() : $this->supremeHttp->getDropListPageByDate($season, $date));
        return (new DropListItemIdsParser($dom))->parse();
    }

    public function getDropListItems(?string $season = null, ?string $date = null)
    {
        $items = [];
        foreach ($this->getItemIds($season, $date) as $id => $category) {
            try {
                $itemParser = new DropListItemParser($this->supremeHttp->getItem($id), $category, $id);

                $item = $itemParser->parse();

                $votes = []; //$this->parseVotes($id);

                $items[] = array_merge($item, $votes);

                if ($this->debug)
                    echo "Successfully parsed: $id - " . $item['title'] . " \n";

                sleep($this->delay);
            } catch (ClientException $exception) {
                if ($this->debug) {
                    if ($exception->getCode() == 404)
                        echo "failed to request: $id  -  404 . \n";
                    else
                        echo "failed to request: $id  -  " . $exception->getCode() . " - " . $exception->getMessage() . " \n";
                }
            }

        }
        return $items;
    }

    public function getAllItems()
    {
        $response = $this->supremeHttp->getSeasonItemsOverview('spring-summer2019');
        $seasons = (new SCSeasonListPicker($response))->parse();

        $ids = collect($seasons)->mapWithKeys(function (array $season) {
            sleep(random_int(500, 1000) / 1000);
            $response = $this->supremeHttp->get($season['route']);
            $ids = (new SeasonListItemIdsParser($response))->parse();
            return [$season['name'] => $ids];
        });
        sleep(5);
        $items = $ids->mapWithKeys(function (array $categoryIds, string $season) {
            $seasonItems = collect($categoryIds)->mapWithKeys(function (array $ids, string $category) {
                $items = collect($ids)->map(function ($id) use ($category) {
                    sleep(random_int(1000, 2000) / 1000);
                    $response = $this->supremeHttp->getItem($id);
                    $item = (new DropListItemParser($response, $category, $id))->parse();
                    return $item;
                })->toArray();
                sleep(5);
                return [$category => $items];
            })->toArray();
            sleep(10);
            return [$season => $seasonItems];
        })->toArray();


        return $items;
    }
}
