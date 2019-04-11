<?php

namespace Supreme\Parser\Tests;

use PHPUnit\Framework\TestCase;
use Supreme\Parser\SupremeParser;

class ParserTest extends TestCase
{

    public function testShit()
    {
        $parser = new SupremeParser("/previews/springsummer2019/all");
        $products = $parser->parse();

        $this->assertNotEmpty($products);
        $this->assertTrue(true);
    }
}
