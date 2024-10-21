<?php declare(strict_types = 1);

namespace SqlException\Base\Model;

class SimpleXMLReader extends \XMLReader
{
    /**
     * Callbacks
     *
     * @var callable
     */
    protected $callback;

    /**
     * @var string
     */
    protected $callbackPath;

    /**
     * @var int
     */
    protected $callbackDepth;

    /**
     * @var string
     */
    protected $currentXpath = "/";

    /**
     * Previous depth
     *
     * @var int
     */
    protected $prevDepth = 0;

    /**
     * Stack of the parsed nodes
     *
     * @var array
     */
    protected $nodesParsed = [];

    /**
     * Do not remove redundant white space.
     *
     * @var bool
     */
    public $preserveWhiteSpace = true;

    /**
     * Add node callback
     *
     * @param string $xpath
     * @param callback $callback
     *
     * @return SimpleXMLReader
     * @throws \Exception
     */
    public function registerCallback($xpath, $callback)
    {
        if (false === is_callable($callback)) {
            throw new \InvalidArgumentException("Not callable callback");
        }
        $this->callbackPath = rtrim($xpath, '/');
        $this->callback = $callback;
        $this->callbackDepth = count(explode('/', trim($this->callbackPath, '/'))) - 1;
        return $this;
    }

    /**
     * Moves cursor to the next node in the document.
     *
     * @link http://php.net/manual/en/xmlreader.read.php
     * @return bool Returns TRUE on success or FALSE on failure.
     * @throws \Exception
     */
    public function read(): bool
    {
        $read = parent::read();
        if ($this->nodeType !== self::ELEMENT && $this->nodeType !== self::END_ELEMENT) {
            return $read;
        }
        if ($this->depth < $this->prevDepth && $this->depth > 0) {
            $this->currentXpath = substr($this->currentXpath, 0, strrpos($this->currentXpath, '/'));
            if (!isset($this->nodesParsed[$this->depth])) {
                throw new \OutOfBoundsException("Invalid xml: missing items in SimpleXMLReader::\$nodesParsed");
            }
            $this->nodesParsed = array_slice($this->nodesParsed, 0, $this->depth + 1, true);
        }
        if (!isset($this->nodesParsed[$this->depth]) || $this->localName !== $this->nodesParsed[$this->depth]) {
            $this->nodesParsed[$this->depth] = $this->localName;
            $this->currentXpath = '/' . implode('/', $this->nodesParsed);
        }
        $this->prevDepth = $this->depth;
        return $read;
    }

    /**
     * Run parser
     *
     * @return void
     * @throws \Exception
     */
    public function parse()
    {
        $continue = true;
        while ($continue && $this->read()) {
            if ($this->nodeType !== self::ELEMENT) {
                continue;
            }
            if (strpos($this->callbackPath, $this->currentXpath) !== 0) {
                $this->next();
                continue;
            }
            if ($this->callbackDepth !== $this->depth) {
                continue;
            }
            if ($this->currentXpath == $this->callbackPath) {
                $continue = call_user_func($this->callback, $this); //phpcs:ignore
            }
        }
    }

    /**
     * @return string
     */
    public function currentXpath()
    {
        return $this->currentXpath;
    }

    /**
     * Run XPath query on current node
     *
     * @param string $path
     * @param string $version
     * @param string $encoding
     * @param string $className
     *
     * @return array(SimpleXMLElement)
     */
    public function expandXpath($path, $version = "1.0", $encoding = "UTF-8", $className = null): array
    {
        return $this->expandSimpleXml($version, $encoding, $className)->xpath($path);
    }

    /**
     * Expand current node to string
     *
     * @param string $version
     * @param string $encoding
     * @param string $className
     *
     * @return bool|\SimpleXMLElement|string
     */
    public function expandString($version = "1.0", $encoding = "UTF-8", $className = null)
    {
        return $this->expandSimpleXml($version, $encoding, $className)->asXML();
    }

    /**
     * Expand current node to SimpleXMLElement
     *
     * @param string $version
     * @param string $encoding
     * @param string $className
     *
     * @return \SimpleXMLElement
     */
    public function expandSimpleXml($version = "1.0", $encoding = "UTF-8", $className = null): \SimpleXMLElement
    {
        $element = $this->expand();
        $document = new \DomDocument($version, $encoding);
        $document->preserveWhiteSpace = $this->preserveWhiteSpace;
        if ($element instanceof \DOMCharacterData) {
            $nodeName = array_splice($this->nodesParsed, -2, 1);
            $nodeName = (isset($nodeName[0]) && $nodeName[0] ? $nodeName[0] : "root");
            $node = $document->createElement($nodeName);
            $node->appendChild($element);
            $element = $node;
        }
        $node = $document->importNode($element, true);
        $document->appendChild($node);
        return simplexml_import_dom($node, $className);
    }

    /**
     * Expand current node to DomDocument
     *
     * @param string $version
     * @param string $encoding
     *
     * @return \DomDocument
     */
    public function expandDomDocument($version = "1.0", $encoding = "UTF-8"): \DomDocument
    {
        $element = $this->expand();
        $document = new \DomDocument($version, $encoding);
        $document->preserveWhiteSpace = $this->preserveWhiteSpace;
        if ($element instanceof \DOMCharacterData) {
            $nodeName = array_splice($this->nodesParsed, -2, 1);
            $nodeName = (isset($nodeName[0]) && $nodeName[0] ? $nodeName[0] : "root");
            $node = $document->createElement($nodeName);
            $node->appendChild($element);
            $element = $node;
        }
        $node = $document->importNode($element, true);
        $document->appendChild($node);
        return $document;
    }
}
