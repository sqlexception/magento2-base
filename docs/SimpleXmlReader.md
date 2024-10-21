
# SimpleXMLReader

The `SimpleXMLReader` class is part of the `SqlException_Base` module and is designed to efficiently read and process large XML files by reducing memory overhead. This is achieved by reading the XML file in chunks, rather than loading the entire document into memory.

## Usage

### Initializing the Reader

To use the `SimpleXMLReader`, first instantiate the class and open an XML file:

```php
use SqlException\Base\Model\SimpleXMLReader;

// Create a new instance of SimpleXMLReader
$reader = new SimpleXMLReader();

// Open the XML file
$reader->open('/path/to/your/xmlfile.xml');
```

### Registering XPath Callbacks

You can register XPath callbacks to process specific XML nodes as they are encountered during parsing:

```php
$reader->registerCallback('/CATALOG/CLASSIFICATION/TAGS/TAG', function ($node) {
    /** @var SimpleXMLReader $node */
    echo 'Processing tag with ID: ' . $node->expandSimpleXml()->ID;
});
```

### Reading the File

To process the XML file, simply call the `read()` method in a loop:

```php
while ($reader->read()) {
    // The reader processes the file chunk by chunk
}
```

## Benefits

- **Memory Efficiency**: Unlike loading the entire XML file into memory, `SimpleXMLReader` reads XML files in chunks, significantly reducing memory usage, especially for large XML documents.
- **Streamlined Processing**: Registering XPath callbacks allows you to efficiently process specific nodes as they are read, without needing to traverse the entire document.

For more details, see the [PHP manual on XMLReader](https://www.php.net/manual/en/class.xmlreader.php).

## Example

Here’s a complete example of how to use the `SimpleXMLReader` to process XML nodes:

```php
use SqlException\Base\Model\SimpleXMLReader;

// Create a new reader instance
$reader = new SimpleXMLReader();

// Open the XML file
$reader->open('/path/to/your/xmlfile.xml');

// Register a callback for processing specific tags
$reader->registerCallback('/CATALOG/CLASSIFICATION/TAGS/TAG', function ($node) {
    /** @var SimpleXMLReader $node */
    $tag = $node->expandSimpleXml();
    echo 'Tag ID: ' . $tag->ID . ', Name: ' . $tag->NAME . PHP_EOL;
});

// Read the XML file in chunks
while ($reader->read()) {
    // Each read processes part of the file, reducing memory usage
}
```

This example demonstrates how to efficiently parse and process large XML files using the `SimpleXMLReader` to minimize memory consumption.
