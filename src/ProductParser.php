<?php

namespace Supreme\Parser;

use Supreme\Parser\Abstracts\ResponseParser;

class ProductParser extends ResponseParser
{
    public function parse()
    {
        $divElement = $this->dom->getElementById("container");
        $dataImagesAttribute = $divElement->getAttribute("data-images");
        $filteredJson = html_entity_decode($dataImagesAttribute, ENT_QUOTES);
        return json_decode($filteredJson, true);
    }
}