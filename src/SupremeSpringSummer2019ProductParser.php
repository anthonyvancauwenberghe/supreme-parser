<?php

namespace Supreme\Parser;

use Supreme\Parser\Abstracts\SupremeHtmlParser;

class SupremeSpringSummer2019ProductParser extends SupremeHtmlParser
{
    public function getProductsArray()
    {
        $divElement = $this->dom->getElementById("container");
        $dataImagesAttribute = $divElement->getAttribute("data-images");
        $filteredJson = html_entity_decode($dataImagesAttribute, ENT_QUOTES);
        return json_decode($filteredJson, true);
    }

    public function parse()
    {
        $productArray = $this->getProductsArray();
        return $productArray;
    }

}