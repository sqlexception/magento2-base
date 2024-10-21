<?php declare(strict_types = 1);

namespace SqlException\Base\Test\Unit\Model;

use SqlException\Base\Model\SimpleXMLReader;

/**
 * Class SimpleXMLReaderTest
 * Unit tests for SimpleXMLReader.
 */
class SimpleXMLReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SimpleXMLReader
     */
    protected SimpleXMLReader $reader;

    /**
     * Set up test cases.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->reader = new SimpleXMLReader();
    }

    /**
     * Test that the reader can correctly read and process product tags using XPath callbacks.
     *
     * @return void
     */
    public function testXpathCallBackForProductTags(): void
    {
        $filePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "_files" . DIRECTORY_SEPARATOR . "sample_feed.xml";
        $this->reader->open($filePath);
        $this->reader->registerCallback('/TBCATALOG/CLASSIFICATION/TAGS/TAG', function ($node) {
            /** @var SimpleXMLReader $node */
            $this->assertEquals('/TBCATALOG/CLASSIFICATION/TAGS/TAG', $node->currentXpath());
            $this->assertTrue((int)$node->expandSimpleXml()->ID > 0);
        });
    }
}
