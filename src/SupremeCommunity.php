<?php


namespace Supreme\Parser;

use GuzzleHttp\Exception\ClientException;
use Supreme\Parser\Http\SupremeCommunityHttpClient;
use Supreme\Parser\Parsers\DropListItemIdsParser;
use Supreme\Parser\Parsers\DropListItemParser;

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
                $itemParser = new DropListItemParser($this->supremeHttp->getItem($id),$category);

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
}
