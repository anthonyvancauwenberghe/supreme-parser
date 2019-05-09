<?php

namespace Supreme\Parser;

use Supreme\Parser\Abstracts\HtmlParser;

class SupremeParser extends HtmlParser
{
    protected $parseDelay;

    protected $baseUrl = "https://www.supremenewyork.com";
    /**
     * SupremeParser constructor.
     * @param string $route
     * @param int $parseDelay
     */
    public function __construct(string $route, int $parseDelay = 0)
    {
        $this->parseDelay = $parseDelay;
        parent::__construct($route);
    }

    public function getProductRoutes(): array
    {
        $urls = [];
        foreach ($this->dom->getElementById("container") as $article) {
            $urls[] = $this->filterUrl($article->firstChild()->firstChild()->getAttribute("href"));
        };
        $urls = array_unique($urls);
        return $urls;
    }

    private function filterUrl($url)
    {
        $lastCharacters = substr($url, -3);

        if (($pos = strpos($lastCharacters, '-')) !== false) {
            return substr($url, 0, $pos - 3);
        }
        return $url;
    }

    protected function transformProducts($productsArray)
    {
        $products = [];

        $array = $productsArray;

        foreach ($array as $product) {
            $unformatted = ($products[$product['title']]['unformatted'] ?? []);
            $unformatted[] = $product;
            $products[$product['title']] = [
                "title" => $product['title'],
                "description" => $product['caption'],
                "url" => $this->baseUrl . $product['url'],
                "colors" => $this->createColorsArray($products, $product['title'], $product['color']),
                "images" => $this->createImageArray($products, $product['title'], $product['color'], $product['imageUrl'], $product['zoomedImageUrl']),
                "unformatted" => $unformatted
            ];
        }
        return $products;
    }

    private function createColorsArray($products, string $productName, string $color)
    {
        $colorsArray = $products[$productName]['colors'] ?? [];
        if (!in_array($color, $colorsArray)) {
            $colorsArray[] = $color;
        }
        return $colorsArray;
    }

    private function createImageArray($products, string $productName, string $color, $imageUrl, $imageZoomedUrl)
    {
        $imagesArray = $products[$productName]['images'] ?? [];
        $imagesArray[] = [
            "normal" => $imageUrl,
            "zoomed" => $imageZoomedUrl,
            "color" => $color
        ];
        return $imagesArray;
    }

    public function parse()
    {
        $products = [];
        foreach ($this->getProductRoutes() as $productRoute) {
            try {
                $parsedProduct = (new ProductParser($productRoute))->parse();
                $products = array_merge($products, $parsedProduct);
                if ($this->parseDelay > 0)
                    sleep($this->parseDelay);
            } catch (\Throwable $e) {
                echo $e->getMessage();
            }

        }
        return $this->transformProducts($products);
    }
}