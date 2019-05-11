<?php

namespace Supreme\Parser\Tests;

use PHPUnit\Framework\TestCase;
use Supreme\Parser\SupremeCommunityDropListItemIdsParser;
use Supreme\Parser\SupremeCommunityLatestDroplistParser;
use Supreme\Parser\SupremeLookbookParser;


class ParserTest extends TestCase
{
    public function testLookbook()
    {
        $parser = new SupremeLookbookParser("/previews/springsummer2019/all", true);
        $products = $parser->parse();

        $this->assertNotEmpty($products);
        $this->assertTrue(true);
    }

    public function testDroplist()
    {
        $parser = SupremeCommunityDropListItemIdsParser::getLatestDropWeekParser();
        $products = $parser->parse();

        $this->assertNotEmpty($products);
    }

    public function testParseLatestDroplist()
    {
        $parser = new SupremeCommunityLatestDroplistParser(1,true);
        $items = $parser->parse();

        $this->assertNotEmpty($items);
    }
}
