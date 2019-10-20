<?php


namespace Supreme\Parser;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Str;
use Supreme\Parser\Http\SupremeCommunityHttpClient;
use Supreme\Parser\Http\SupremeNewYorkHttpClient;
use Supreme\Parser\Parsers\DropListItemIdsParser;
use Supreme\Parser\Parsers\DropListItemParser;
use Supreme\Parser\Parsers\SupremeNewsParser;
use Supreme\Parser\Parsers\SupremePreviewItemParser;
use Supreme\Parser\Parsers\SupremePreviewUrlsParser;

class SupremeNewYork
{
    public $client;

    public const SEASONS = [
        "fallwinter2019",
        "springsummer2019",
        "fallwinter2018",
        "springsummer2018",
        "fallwinter2017",
        "springsummer2017",
    ];

    public function __construct()
    {
        $this->client = new SupremeNewYorkHttpClient();
    }

    public function parseNews()
    {
        $news = [];
        for ($i = 1; $i < 30; $i++) {
            $response = $this->client->getNews($i);
            $parsed = (new SupremeNewsParser($response))->parse();
            $news = array_merge($news, $parsed);
        }
        return $news;
    }

    public function parsePreview(string $season)
    {
        $client = new SupremeNewYorkHttpClient();
        $parser = new SupremePreviewUrlsParser($client->getPreview($season));
        $routes = $parser->parse();

        $items = [];
        $products = [];
        $parsedRoutes = [];
        foreach ($routes as $route) {
            $response = $client->getPreviewItem($route);
            $items = (new SupremePreviewItemParser($response))->parse();
            $colors = [];
            foreach ($items as $item) {
                $product = $this->findExistingProduct($products, $item);
                $images = ($product['images'] ?? []);
                $colors = ($product['colors'] ?? []);

                if ($item->color !== "Group")
                    $colors[] = $item->color;
                $colors = collect($colors)->unique()->toArray();

                $imageUrl = Str::replaceFirst('//', 'https://', $item->imageUrl);
                if (!in_array($imageUrl, $images[$item->color] ?? []))
                    $images[$item->color][] = $imageUrl;

                $product = [
                    "title" => $item->title,
                    "caption" => $item->caption,
                    "category" => $this->extractCategoryFromUrl($item->url, $season),
                    "images" => $images,
                    "colors" => $colors,
                ];
                $added = false;
                foreach ($products as $key => $aProduct) {
                    if ($aProduct['title'] === $product['title']) {
                        $products[$key] = $product;
                        $added = true;
                        break;
                    }
                }
                if (!$added && is_array($product))
                    $products[] = $product;
            }
        }


        return $products;
    }

    protected function findExistingProduct($products, $item)
    {
        foreach ($products as $key => $aProduct) {
            if ($aProduct['title'] === $item->title) {
                return $aProduct;
            }
        }
        return null;
    }

    protected function extractCategoryFromUrl($url, $season)
    {
        $withoutFirst = Str::replaceFirst("/previews/$season/", "", $url);
        return Str::before($withoutFirst, '/');
    }
}
