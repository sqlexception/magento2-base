
# How to Integrate the Advanced Logger in Your Magento 2 Module

## Overview

This guide explains how to integrate the **Advanced Logger** into your custom Magento 2 module using a **virtual type**. The logger allows you to configure the log level, log file path, and whether context information should be logged, all via the Magento admin panel.

We’ll use the vendor name `YourVendor` and the module name `YourName`. The guide will cover:

1. Creating the virtual type for the logger in your `di.xml`.
2. Configuring the logger in the admin with `system.xml`.
3. Using the logger in your custom class.

## Step 1: Create a Virtual Type for the Logger in `di.xml`

A virtual type allows you to create an instance of the logger with your own custom configuration (e.g., log level, log file, and context settings). To create the virtual type:

### 1.1 Create or Update `di.xml`

Add the following content to your module's `di.xml` file (`app/code/YourVendor/YourName/etc/di.xml`):

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <virtualType name="YourVendor\YourName\Logger" type="SqlException\Base\Logger\Logger">
        <arguments>
            <!-- Prefix for XML configuration paths (e.g., 'yourvendor/yourname') -->
            <argument name="configPrefix" xsi:type="string">yourvendor/yourname</argument>
        </arguments>
    </virtualType>
</config>
```

### Explanation:

- The virtual type `YourVendor\YourName\Logger` is based on the core logger class `SqlException\Base\Logger\Logger`.
- The `configPrefix` argument defines the configuration path used to pull settings like log level and log file path from the Magento configuration. In this case, the prefix is `yourvendor/yourname`.

## Step 2: Create the `system.xml` for Admin Configuration

The `system.xml` file defines the configuration options (log level, log file path, and whether context should be included) in the Magento admin panel.

### 2.1 Create or Update `system.xml`

Create the `system.xml` file in `app/code/YourVendor/YourName/etc/adminhtml/system.xml`:

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Config/etc/system_file.xsd">
    <system>
        <section id="yourvendor_yourname" translate="label" sortOrder="100" showInDefault="1" showInWebsite="1" showInStore="1">
            <label>Advanced Logger Settings</label>
            <tab>general</tab>
            <resource>YourVendor_YourName::config</resource>

            <group id="settings" translate="label" sortOrder="10" showInDefault="1" showInWebsite="1" showInStore="1">
                <label>Logger Settings</label>

                <!-- Log Level -->
                <field id="log_level" translate="label" type="select" sortOrder="10" showInDefault="1" showInWebsite="1" showInStore="1">
                    <label>Log Level</label>
                    <source_model>SqlException\Base\Model\Config\Source\LogLevel</source_model>
                    <comment>Select the level of logs to capture.</comment>
                </field>

                <!-- Log File -->
                <field id="log_file" translate="label" type="text" sortOrder="20" showInDefault="1" showInWebsite="1" showInStore="1">
                    <label>Log File Path</label>
                    <comment>Specify the log file name (e.g., custom.log). Must reside in var/log/.</comment>
                </field>

                <!-- Log Context -->
                <field id="log_context" translate="label" type="select" sortOrder="30" showInDefault="1" showInWebsite="1" showInStore="1">
                    <label>Log Context</label>
                    <source_model>Magento\Config\Model\Config\Source\Yesno</source_model>
                    <comment>Enable or disable context information in logs.</comment>
                </field>

            </group>
        </section>
    </system>
</config>
```

### Explanation:
- This configuration adds a new section called **Advanced Logger Settings** under the **General** tab in the admin panel.
- The options include:
    - **Log Level**: Provides a dropdown of log levels (e.g., debug, info, warning, error, etc.).
    - **Log File Path**: Allows specifying the log file name (restricted to the `var/log/` directory). For example, you can set the log file as `custom.log` and it will be written to `var/log/custom.log`.
    - **Log Context**: Allows enabling or disabling context logging.

## Step 3: Use the Logger in Your Module

Once the virtual type and configuration are set up, you can inject the logger into any class in your module and use it.

### 3.1 Example of Using the Logger

Create a class `Example.php` in your module to demonstrate how to inject and use the logger.

```php
<?php

namespace YourVendor\YourName;

use YourVendor\YourName\Logger;

class Example
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * Constructor
     *
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function execute()
    {
        // Log a message with log level INFO
        $this->logger->info('This is an info log message.');

        // Log a message with log level DEBUG and context
        $this->logger->debug('This is a debug log message.', ['user_id' => 123, 'order_id' => 456]);
    }
}
```

### Explanation:
- In the example above, the logger is injected via the constructor and used to log messages with different log levels and context information.

### Step 4: Test and Verify

Once you've implemented the logger in your module, follow these steps:

1. Navigate to **Stores > Configuration > Advanced Logger Settings** in the Magento admin panel.
2. Set your desired **log level**, **log file path**, and **log context** options.
    - Example log file: `custom.log` will log to `var/log/custom.log`.
3. Ensure the logger works as expected by logging messages from your classes.

---

### Summary

- **Virtual Type**: Allows you to create an instance of the logger with custom configuration paths.
- **Admin Configuration**: You can configure the logger (log level, file path, and context) via the Magento admin panel.
- **Usage**: You can inject and use the logger in any class in your module for logging purposes.

By following this guide, you can easily integrate the advanced logger into your Magento module and have full control over logging behavior through admin configurations.
