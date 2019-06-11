<?php

namespace Supreme\Parser\Tests;

use PHPUnit\Framework\TestCase;
use Supreme\Parser\Http\SupremeCommunityHttpClient;
use Supreme\Parser\SupremeCommunity;
use Supreme\Parser\SupremeLookbookParser;


class ParserTest extends TestCase
{
    public function testDroplistUrlParser(){
        $http = new SupremeCommunityHttpClient();
       $url = $http->getLatestDroplistUrl();
       $this->assertStringContainsString("season",$url);
    }

    public function testGetLatestDroplistIds(){
        $client = new SupremeCommunity(2, true);
        $ids = $client->getItemIds();

        $this->assertNotEmpty($ids);

        foreach($ids as $id ){
            $this->assertIsNumeric($id);
        }
    }

    public function testGetLatestDroplistItems(){
        $client = new SupremeCommunity(2, true);
        $items = $client->getLatestDroplistItems();
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
}
