<?php

declare(strict_types=1);

namespace SqlException\Base\Test\Unit\Config;

use SqlException\Base\Config\BaseConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Encryption\KeyValidator;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class BaseConfigTest extends TestCase
{
    private const CRYPT_KEY = 'g9mY9KLrcuAVJfsmVUSRkKFLDdUPVkaZ';

    /** @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $scopeConfigMock;

    /** @var EncryptorInterface */
    private $encryptor;

    /** @var BaseConfig */
    private $configModel;

    /** @var ModuleListInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $moduleListMock;

    /**
     * Set up method for initializing mocks and the config model
     */
    protected function setUp(): void
    {
        // Initialize the ObjectManager to create objects
        $objectManager = new ObjectManager($this);

        // Mocks for dependencies
        $randomGeneratorMock = $this->createMock(\Magento\Framework\Math\Random::class);
        $keyValidatorMock = $this->createMock(KeyValidator::class);
        $deploymentConfigMock = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
        $deploymentConfigMock->expects($this->any())
            ->method('get')
            ->with(Encryptor::PARAM_CRYPT_KEY)
            ->willReturn(self::CRYPT_KEY);

        // Create a real instance of Encryptor with mocked dependencies
        $this->encryptor = new Encryptor(
            $randomGeneratorMock,
            $deploymentConfigMock,
            $keyValidatorMock
        );

        // Mock the ScopeConfig and ModuleList
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->moduleListMock = $this->createMock(ModuleListInterface::class);

        // Use JSON serializer for serialize and deserialize methods
        $serializer = $objectManager->getObject(Json::class);

        // Arguments to pass to the BaseConfig instance
        $arguments = [
            'scopeConfig' => $this->scopeConfigMock,
            'encryptor' => $this->encryptor,
            'serializer' => $serializer,
            'appState' => $this->createMock(State::class),
            'request' => $this->createMock(RequestInterface::class),
            'moduleList' => $this->moduleListMock,
        ];

        // Create an anonymous class that extends BaseConfig
        $this->configModel = new class (
            $arguments['scopeConfig'],
            $arguments['encryptor'],
            $arguments['serializer'],
            $arguments['appState'],
            $arguments['request'],
            $arguments['moduleList']
        ) extends BaseConfig {
        };
    }

    /**
     * Test to ensure getModuleVersion retrieves the correct version from the module list
     */
    public function testGetModuleVersion(): void
    {
        $this->moduleListMock->expects($this->once())
            ->method('getOne')
            ->willReturn(['setup_version' => '1.0.0']);

        $this->assertEquals('1.0.0', $this->configModel->getModuleVersion());
    }

    /**
     * Test to verify that getSetupVersion correctly retrieves the module setup version
     */
    public function testGetSetupVersion(): void
    {
        $this->moduleListMock->expects($this->once())
            ->method('getOne')
            ->willReturn(['setup_version' => '1.0.0']);

        $this->assertEquals('1.0.0', $this->configModel->getSetupVersion());
    }

    /**
     * Test to confirm that getModuleXmlVersion retrieves the correct setup version
     */
    public function testGetModuleXmlVersion(): void
    {
        $this->moduleListMock->expects($this->once())
            ->method('getOne')
            ->willReturn(['setup_version' => '1.0.0']);

        $this->assertEquals('1.0.0', $this->configModel->getModuleXmlVersion());
    }

    /**
     * Test to check if getModuleName properly generates the module name from the namespace
     */
    public function testGetModuleName(): void
    {
        $this->assertEquals('SqlException_Base', $this->configModel->getModuleName());
    }

    /**
     * Test to verify getConfigValue retrieves the correct configuration value from scopeConfig
     */
    public function testGetConfigValue(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('some/xml/path', ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
            ->willReturn('test_value');

        $this->assertEquals('test_value', $this->configModel->getValue('some/xml/path'));
    }

    /**
     * Test to ensure getDecryptedConfigValue correctly decrypts encrypted values from the configuration
     */
    public function testGetDecryptedConfigValue(): void
    {
        $encryptedValue = $this->encryptor->encrypt('test_value');

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('some/encrypted/path', ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
            ->willReturn($encryptedValue);

        $this->assertEquals('test_value', $this->configModel->getValueDecrypted('some/encrypted/path'));
    }

    /**
     * Test to confirm that the serialize method correctly serializes an array to a JSON string
     */
    public function testSerialize(): void
    {
        $data = ['key' => 'value'];
        $this->assertEquals(json_encode($data), $this->configModel->serialize($data));
    }

    /**
     * Test to ensure the deserialize method correctly unserializes a JSON string to an array
     */
    public function testDeserialize(): void
    {
        $data = json_encode(['key' => 'value']);
        $this->assertEquals(['key' => 'value'], $this->configModel->deserialize($data));
    }

    /**
     * Test to check if getCurrentArea returns the correct frontend area code
     */
    public function testGetCurrentAreaFrontend(): void
    {
        $appStateMock = $this->createMock(State::class);
        $appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn(\Magento\Framework\App\Area::AREA_FRONTEND);

        // Inject the mock into the config model using reflection
        $reflectionClass = new \ReflectionClass($this->configModel);
        $property = $reflectionClass->getProperty('appState');
        $property->setAccessible(true);
        $property->setValue($this->configModel, $appStateMock);

        $this->assertEquals('frontend', $this->configModel->getCurrentArea());
    }
}
