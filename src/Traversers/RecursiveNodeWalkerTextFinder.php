<?php


namespace Supreme\Parser\Traversers;


use Illuminate\Support\Str;
use PHPHtmlParser\Dom\HtmlNode;
use PHPHtmlParser\Dom\TextNode;
use SebastianBergmann\CodeCoverage\Report\Text;

class RecursiveNodeWalkerTextFinder
{
    /**
     * @var HtmlNode
     */
    protected $rootNode;

    /**
     * @var array | HtmlNode[]
     */
    protected $results = [];

    /**
     * @var string
     */
    protected $text;

    /**
     * @var bool
     */
    protected $strict;

    /**
     * RecursiveNodeWalker constructor.
     * @param $rootNode
     * @param $attribute
     * @param $match
     * @param $strict
     */
    public function __construct(HtmlNode $rootNode, ?string $text = null, bool $strict = false)
    {
        $this->rootNode = $rootNode;
        $this->text = $text;
        $this->strict = $strict;
    }

    public function traverse()
    {
        return $this->walkRecursively($this->rootNode);
    }

    protected function walkRecursively(HtmlNode $node)
    {
        /**
         * @var TextNode | HtmlNode $child
         */
        foreach ($node->getChildren() as $child) {
            $text = $child->text(false);

            if($text !==null)
                $text = ltrim($text);

            if(is_string($text) && $text !== "")
                $this->results[] =$text;

            if ($child instanceof HtmlNode)
                $this->walkRecursively($child);
        }

        $this->results = array_unique($this->results);

        return $this->results;
    }

    public function stripFirstSpaces(){

    }

    public function traverseTillFirst()
    {
        return $this->traverse()[0] ?? null;
    }
}
