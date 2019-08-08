<?php


namespace Supreme\Parser\Traversers;

use PHPHtmlParser\Dom\HtmlNode;

class RecursiveNodeWalker
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
    protected $attribute;

    /**
     * @var string | null
     */
    protected $match;

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
    public function __construct(HtmlNode $rootNode, ?string $attribute = null, ?string $match = null, bool $strict = false)
    {
        $this->rootNode = $rootNode;
        $this->attribute = $attribute;
        $this->match = $match;
        $this->strict = $strict;
    }

    public function traverse(?callable $transformer=null)
    {
        $nodes = $this->walkRecursively($this->rootNode);

        if ($transformer === null)
            return $this->results;
        else {
            $results = [];
            foreach ($nodes as $node) {
                $result = $transformer($node);
                if ($result === FALSE) {
                    return $results;
                }
                if ($result === TRUE || !isset($result))
                    continue;
                $results[] = $result;
            }
        }
        return $results;
    }

    public function traverseTillFirst(?callable $transformer = null)
    {
        $node = $this->walkRecursively($this->rootNode)[0] ?? null;

        if ($transformer === null)
            return $node;

        return $transformer($node);
    }


    protected function walkRecursively(HtmlNode $node)
    {
        if ($this->attribute === null ||($node->getTag()->hasAttribute($this->attribute) && $this->matchString($this->match, $node->getTag()->getAttribute($this->attribute)['value']))) {
            $this->results[] = $node;
        }

        foreach ($node->getChildren() as $child) {
            if ($child instanceof HtmlNode)
                $this->walkRecursively($child);
        }

        return $this->results;
    }

    protected function matchString($needle, $haystack)
    {
        if ($this->match === null)
            return true;
        if ($this->strict)
            return $needle === $haystack;
        return $this->stringContains($needle, $haystack);
    }

    protected function stringContains(string $needle, string $haystack)
    {
        return strpos($haystack, $needle) !== false;
    }


}
