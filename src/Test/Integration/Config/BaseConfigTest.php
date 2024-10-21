<?php declare(strict_types=1);

namespace SqlException\Base\Test\Integration\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\App\RequestInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use SqlException\Base\Config\BaseConfig;

class BaseConfigTest extends TestCase
{
    private $config;
    private $encryptor;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $scopeConfig = $objectManager->get(ScopeConfigInterface::class);
        $serializer = $objectManager->get(SerializerInterface::class);
        $appState = $objectManager->get(AppState::class);
        $request = $objectManager->get(RequestInterface::class);
        $moduleList = $objectManager->get(ModuleListInterface::class);
        $this->encryptor = $objectManager->get(EncryptorInterface::class);

        $this->config = new class($scopeConfig, $this->encryptor, $serializer, $appState, $request, $moduleList) extends BaseConfig {
        };
    }

    public function testGetConfigValue(): void
    {
        $configValue = $this->config->getValue('web/secure/base_url');
        $this->assertNotNull($configValue, 'Config value should not be null.');
        $this->assertIsString($configValue, 'Config value should be a string.');
    }

    public function testGetDecryptedConfigValue(): void
    {
        $encryptedConfigPath = 'payment/paypal/merchant_gateway_key';
        $encryptedValue = $this->encryptor->encrypt('test_value');

        $scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with($encryptedConfigPath)
            ->willReturn($encryptedValue);

        $reflectionClass = new \ReflectionClass($this->config);
        $property = $reflectionClass->getProperty('scopeConfig');
        $property->setAccessible(true);
        $property->setValue($this->config, $scopeConfigMock);

        $decryptedValue = $this->config->getValueDecrypted($encryptedConfigPath);

        $this->assertNotNull($decryptedValue, "Decrypted config value for '{$encryptedConfigPath}' should not be null.");
        $this->assertEquals('test_value', $decryptedValue, "Decrypted value should match the original value.");
    }

    public function testSerializeAndDeserialize(): void
    {
        $data = ['key' => 'value'];

        $serialized = $this->config->serialize($data);
        $this->assertIsString($serialized, 'Serialized data should be a string.');

        $deserialized = $this->config->deserialize($serialized);
        $this->assertEquals($data, $deserialized, 'Deserialized data should match the original data.');
    }

    public function testGetModuleVersion(): void
    {
        $moduleVersion = $this->config->getModuleVersion();
        $this->assertNotNull($moduleVersion, 'Module version should not be null.');
    }

    public function testGetModuleName(): void
    {
        $moduleName = $this->config->getModuleName();
        $this->assertNotNull($moduleName, 'Module name should not be null.');
    }

    public function testGetSetupVersion(): void
    {
        $setupVersion = $this->config->getSetupVersion();
        $this->assertNotNull($setupVersion, 'Setup version should not be null.');
    }

    public function testGetCurrentArea(): void
    {
        $area = $this->config->getCurrentArea();
        $this->assertNotNull($area, 'Current area should not be null.');
        $this->assertIsString($area, 'Area should be a string.');
    }

    public function testInvalidDecryption(): void
    {
        $encryptedConfigPath = 'payment/paypal/merchant_gateway_key';

        // Mock the ScopeConfig to return an invalid encrypted value
        $scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with($encryptedConfigPath)
            ->willReturn('invalid_encrypted_value');

        // Inject the mock into the config model using reflection
        $reflectionClass = new \ReflectionClass($this->config);
        $property = $reflectionClass->getProperty('scopeConfig');
        $property->setAccessible(true);
        $property->setValue($this->config, $scopeConfigMock);

        // Test decryption of the invalid value
        $decryptedValue = $this->config->getValueDecrypted($encryptedConfigPath);

        // Assert that the decryption returns null for invalid encrypted values
        $this->assertNull($decryptedValue, "Decrypted value for invalid encryption should be null.");
    }


    public function testInvalidConfigValue(): void
    {
        $invalidConfigPath = 'invalid/config/path';

        $scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with($invalidConfigPath)
            ->willReturn(null);

        $reflectionClass = new \ReflectionClass($this->config);
        $property = $reflectionClass->getProperty('scopeConfig');
        $property->setAccessible(true);
        $property->setValue($this->config, $scopeConfigMock);

        $configValue = $this->config->getValue($invalidConfigPath);
        $this->assertNull($configValue, "Config value for invalid path should be null.");
    }

    public function testAreaHandlingExceptions(): void
    {
        $appStateMock = $this->getMockBuilder(AppState::class)
            ->disableOriginalConstructor()
            ->getMock();
        $appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willThrowException(new \Exception('Area code not set'));

        $reflectionClass = new \ReflectionClass($this->config);
        $property = $reflectionClass->getProperty('appState');
        $property->setAccessible(true);
        $property->setValue($this->config, $appStateMock);

        $area = $this->config->getCurrentArea();
        $this->assertEquals('unknown', $area, "Area should default to 'unknown' when an exception occurs.");
    }
}
