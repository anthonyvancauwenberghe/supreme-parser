<?php

namespace Supreme\Parser\Tests;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Supreme\Parser\Resolvers\LatestDropDateResolver;


class ResolverTest extends TestCase
{
    public function testShit()
    {
        $resolver = new LatestDropDateResolver();
        $date = $resolver->resolve();
        $time = new Carbon($date);
        $this->assertTrue($time->isThursday());
    }
}
