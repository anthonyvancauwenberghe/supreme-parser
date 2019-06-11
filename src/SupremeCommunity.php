<?php


namespace Supreme\Parser;

use GuzzleHttp\Exception\ClientException;
use Supreme\Parser\Http\SupremeCommunityHttpClient;
use Supreme\Parser\Parsers\DropListItemIdsParser;
use Supreme\Parser\Parsers\DropListItemParser;
use Supreme\Parser\Parsers\DropListItemVoteParser;

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

    public function getItemIds()
    {
        $droplistDom = $this->supremeHttp->getLatestDroplistPage();
        return (new DropListItemIdsParser($droplistDom))->parse();
    }

    protected function parseVotes($id)
    {
        $voteParser = new DropListItemVoteParser($this->supremeHttp->getItemVote($id));
        try {
            $votes = $voteParser->parse();
        } catch (\Throwable $e) {
            $votes = [
                "upvotes" => 0,
                "downvotes" => 0,
            ];
        }
        return $votes;
    }

    public function getLatestDroplistItems()
    {
        $items = [];
        foreach ($this->getItemIds() as $id) {
            try {
                $itemParser = new DropListItemParser($this->supremeHttp->getItem($id));

                $item = $itemParser->parse();

                $votes = []; //$this->parseVotes($id);

                $items[] = array_merge($item, $votes);
                if ($this->debug)
                    echo "Successfully parsed: ' $id \n";

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