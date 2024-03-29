<?php


namespace Supreme\Parser;

use GuzzleHttp\Exception\ClientException;
use Supreme\Parser\Http\SupremeCommunityHttpClient;
use Supreme\Parser\Parsers\DropListItemIdsParser;
use Supreme\Parser\Parsers\DropListItemParser;
use Supreme\Parser\Parsers\LeftToDropIdParser;
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

    public function getLeftToDropItems()
    {
        $dom = $this->supremeHttp->getLeftToDropPage();
        $ids = (new LeftToDropIdParser($dom))->parse();

        $data = collect($ids)->map(function ($id, $category) {
            return $this->getItemFromId($category, $id);
        });

        return $data;
    }

    public function getItemFromId($id, ?string $category = "Unknown")
    {
        try {
            $itemParser = new DropListItemParser($this->supremeHttp->getItem($id), $category, $id);

            $item = $itemParser->parse();

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
            return false;
        }
        return $item;
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
        if ($this->debug)
            echo "starting to parse all items from supremecommunity" . " \n";

        $response = $this->supremeHttp->getSeasonItemsOverview('spring-summer2019');
        $seasons = (new SCSeasonListPicker($response))->parse();

        if ($this->debug)
            echo "Found " . count($seasons) . " seasons to parse" . " \n"." \n";

        $ids = collect($seasons)->mapWithKeys(function (array $season) {
            sleep(random_int(500, 1000) / 1000);

            if ($this->debug)
                echo "Started parsing ids from season " . $season['name'] . " \n";

            $response = $this->supremeHttp->get($season['route']);
            $ids = (new SeasonListItemIdsParser($response))->parse();

            if ($this->debug)
                echo "Finished parsing ids from season " . $season['name'] . " \n". " \n";

            return [$season['name'] => $ids];
        });
        sleep(5);

        if ($this->debug)
            echo "Starting to parse Seasons" . " \n";

        $items = $ids->mapWithKeys(function (array $categoryIds, string $season) {

            if ($this->debug)
                echo "Started Parsing season $season" . " \n";

            $seasonItems = collect($categoryIds)->mapWithKeys(function (array $ids, string $category) {
                $items = collect($ids)->map(function ($id) use ($category) {
                    sleep(random_int(1000, 2000) / 1000);
                    $response = $this->supremeHttp->getItem($id);
                    $item = (new DropListItemParser($response, $category, $id))->parse();

                    if ($this->debug)
                        echo "Successfully parsed: $id - " . $item['title'] . " \n";

                    return $item;
                })->toArray();
                sleep(5);
                return [$category => $items];
            })->toArray();
            sleep(10);
            return [$season => $seasonItems];
        })->toArray();

        if ($this->debug)
            echo "Succesfully parsed all seasons";

        return $items;
    }
}
