<?php

namespace Supreme\Parser\Tests;

use PHPUnit\Framework\TestCase;
use Supreme\Parser\SupremeSpringSummer2019Parser;

class ParserTest extends TestCase
{

    public function testShit(){
        $parser = new SupremeSpringSummer2019Parser();
        $products = $parser->parse();

        $this->assertTrue(true);
    }
}
