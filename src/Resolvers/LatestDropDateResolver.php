<?php


namespace Supreme\Parser\Resolvers;


use Carbon\Carbon;

class LatestDropDateResolver
{
    public function resolve()
    {
        $time = Carbon::now();

        $time->subDays(3);

        while ($time->dayOfWeekIso !== 4) {
            $time->addDay();
        }

        return $time->format('Y-m-d');
    }
}