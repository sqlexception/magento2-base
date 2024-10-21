# BaseConfig - Configuration Utility for Magento 2

The `BaseConfig` class provides a flexible and reusable way to handle Magento 2 configurations. It centralizes access to configuration values, supports encrypted values, and integrates various helpers like request handling, module versioning, and serialization. It can be used directly by extending it or configured as a virtual type to easily adapt its behavior to different contexts.

## Key Features

- **Get Configuration Values**: Access standard or boolean config values.
- **Encrypted Config Values**: Automatically decrypt encrypted configuration values.
- **Module Information**: Retrieve the current module version and setup version.
- **Serialization/Deserialization**: Easily serialize and deserialize data.
- **Application Area Handling**: Automatically determine the current application area (e.g., frontend, admin).

## Installation

To use this utility, you can either integrate it into your own Magento 2 module or use it as a virtual type.

### Using as a Virtual Type

You can declare `BaseConfig` as a virtual type in your module's `di.xml` file. This allows you to set a custom configuration prefix without modifying the class directly.

Here’s an example:

```xml
<virtualType name="YourVendor\YourModule\Logger\Config" type="SqlException\Base\Config\BaseConfig"/>
```
This configuration makes it easy to use the BaseConfig class with custom configuration paths defined under yourvendor/yourmodule.

### Usage Examples

#### Accessing Config Values
You can retrieve configuration values like this:

```php
/** @var \YourVendor\YourModule\Logger\Config $config */
$logLevel = $config->getValue('log_level');
$logFile = $config->getValue('log_file');
```
#### Accessing Boolean Flags
To retrieve boolean config values (e.g., yes/no), use:
$logContext = $config->isSetFlag('log_context');

#### Accessing Encrypted Config Values
If a value is encrypted, you can access it using:

```php
$apiToken = $config->getValueDecrypted('api_token');
```
#### Using the Module Information Helpers
You can get the current module version or setup version using:
```php
$moduleVersion = $config->getModuleVersion();
$setupVersion = $config->getSetupVersion();
```

#### Handling Serialization
You can easily serialize and deserialize data:

```php
$serializedData = $config->serialize($data);
$deserializedData = $config->deserialize($serializedData);
```
#### Retrieving the Current Application Area
To get the current area (e.g., frontend, adminhtml, cron), use:
```php
$areaCode = $config->getCurrentArea();
```
#### System Configuration
To fully utilize this class, add the necessary configuration options to your system XML file, so they are manageable from the Magento 2 admin panel.

Here’s an example:

```xml
<config>
    <system>
        <section id="yourvendor">
            <group id="yourmodule" translate="label" type="text" sortOrder="10" showInDefault="1" showInWebsite="1" showInStore="1">
                <label>Your Module Settings</label>
                <field id="log_level" translate="label" type="select" sortOrder="10" showInDefault="1" showInWebsite="1" showInStore="1">
                    <label>Log Level</label>
                    <source_model>YourVendor\YourModule\Model\Config\Source\LogLevel</source_model>
                </field>
                <field id="log_file" translate="label" type="text" sortOrder="20" showInDefault="1" showInWebsite="1" showInStore="1">
                    <label>Log File</label>
                </field>
                <field id="log_context" translate="label" type="select" sortOrder="30" showInDefault="1" showInWebsite="1" showInStore="1">
                    <label>Log Context</label>
                    <source_model>Magento\Config\Model\Config\Source\Yesno</source_model>
                </field>
            </group>
        </section>
    </system>
</config>
```
