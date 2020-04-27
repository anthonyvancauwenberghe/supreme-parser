<?php


namespace Supreme\Parser\Tests;

use PHPUnit\Framework\TestCase;
use Supreme\Parser\Managers\SupremeCommunity\SCDropTimesManager;

class ModelsTest extends TestCase
{
    public function testSupremeCommunityDropTimesSeasonsParsing()
    {
        $sc = new SCDropTimesManager("eu");
        $seasons = $sc->seasons();

        $this->assertCount(5, $seasons);
    }

    public function testSupremeCommunityDropTimesWeekParsing()
    {
        $sc = new SCDropTimesManager("eu");
        $season = $sc->seasons()[0];
        sleep(2);
        $list = $season->weeks();

        $this->assertNotEmpty($list);
    }

    public function testSupremeCommunityDropTimesParsing()
    {
        $sc = new SCDropTimesManager("eu");
        $times = $sc->all();

        file_put_contents('droptimes.json', json_encode($times, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES));
        $this->assertNotEmpty($times);
    }

    public function testSomething()
    {
        $contents = file_get_contents('droptimes.json');
        $contents = preg_replace('/[\x00-\x1F\x7F]/u', '', $contents);
        $data = json_decode($contents, true);
        file_put_contents('droptimes_sanitized.json', json_encode($data, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES));
        $this->assertTrue(true);
    }

}
