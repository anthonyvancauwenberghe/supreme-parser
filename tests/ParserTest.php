<?php

namespace Supreme\Parser\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\TestCase;
use Supreme\Parser\Http\SupremeCommunityHttpClient;
use Supreme\Parser\Http\SupremeNewYorkHttpClient;
use Supreme\Parser\Parsers\DropListItemParser;
use Supreme\Parser\Parsers\SCSeasonListPicker;
use Supreme\Parser\Parsers\SeasonListItemIdsParser;
use Supreme\Parser\SupremeCommunity;
use Supreme\Parser\SupremeLookbookParser;
use Supreme\Parser\SupremeNewYork;
use Symfony\Component\Stopwatch\Stopwatch;


class ParserTest extends TestCase
{
    public function testDroplistUrlParser()
    {
        $http = new SupremeCommunityHttpClient();
        $url = $http->getLatestDroplistUrl();
        $this->assertStringContainsString("season", $url);
    }

    public function testGetLatestDroplistIds()
    {
        $client = new SupremeCommunity(2, true);
        $ids = $client->getItemIds('spring-summer2019', '2019-07-05');

        $this->assertNotEmpty($ids);

        foreach ($ids as $id => $category) {
            $this->assertIsNumeric($id);
            $this->assertIsString($category);
            $this->assertNotEquals('Unknown', $category);
        }
    }

    public function testParseSupComItem()
    {
        $http = new SupremeCommunityHttpClient();
        $response = $http->getItem('489');
        $parser = new DropListItemParser($response);
        $item = $parser->parse();
        $this->assertTrue(true);
    }

    public function testGetLatestDroplistItems()
    {
        $client = new SupremeCommunity(2, true);
        $items = $client->getDropListItems();
        $this->assertNotEmpty($items);
    }

    public function testGetItemsBySeasonDate()
    {
        $client = new SupremeCommunity(2, true);
        $items = $client->getDropListItems('spring-summer2019', '2019-07-05');
        $this->assertNotEmpty($items);
    }

    public function testLookbook()
    {
        $this->markTestSkipped("");
        $parser = new SupremeLookbookParser("/previews/springsummer2019/all", true);
        $products = $parser->parse();

        $this->assertNotEmpty($products);
        $this->assertTrue(true);
    }

    public function testParseNews()
    {
        $supreme = new SupremeNewYork();
        $news = $supreme->parseNews();

        $this->assertNotEmpty($news);
    }

    public function getItems()
    {
        $client = new SupremeNewYorkHttpClient();
        $client->setBaseUri('http://api.supremewatcher.test/v1/supreme');
        $client->setDebugMode(true);
        $stock = $client->getStock()->getBody()->getContents();

        $ids = [];

        foreach (json_decode($stock, true)['products_and_categories'] as $category => $items) {
            foreach ($items as $item) {
                $ids[] = $item['id'];
            }

        }
        $ids = array_unique($ids);

        $stopwatch = new Stopwatch(true);
        $stopwatch->start('parse');
        $items = $client->getItems($ids, 12);
        $stopwatch->stop('parse');

        echo $stopwatch->getEvent('parse')->getDuration();

        $this->assertTrue(true);
    }

    public function testSeasonListIdsParser()
    {
        $client = new SupremeCommunityHttpClient();
        $response = $client->getSeasonItemsOverview('spring-summer2017');
        $parser = new SeasonListItemIdsParser($response);
        $ids = $parser->parse();
    }

    public function testParseItemsSeasonList()
    {
        $client = new SupremeCommunityHttpClient();
        $response = $client->getSeasonItemsOverview('spring-summer2019');

        $parser = new SCSeasonListPicker($response);
        $seasons = $parser->parse();
        $seasons = collect($seasons)->flatten()->toArray();
        $this->assertContains('spring/summer 2019', $seasons);
    }

    public function testParseAllItems()
    {
        $sc = new SupremeCommunity();
        $items = $sc->getAllItems();
        $data = json_encode($items, true);
        file_put_contents('sc_items.json', json_encode($data, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES));
        $this->assertTrue(true);
    }

}
