<?php declare(strict_types=1);

namespace SqlException\Base\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Serialize\SerializerInterface;

class BaseConfig
{
    /**
     * @var string|null Holds the version of the current module.
     */
    protected ?string $moduleVersion = null;

    /**
     * @var string|null Holds the setup version of the module.
     */
    protected ?string $setupVersion = null;

    /**
     * @var string|null Stores the module name.
     */
    protected ?string $moduleName = null;

    /**
     * @var string|null Stores the current area (frontend, adminhtml, etc.).
     */
    protected ?string $area = null;

    /**
     * @var ScopeConfigInterface Handles scope configuration.
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var EncryptorInterface Handles encryption and decryption of values.
     */
    protected EncryptorInterface $encryptor;

    /**
     * @var SerializerInterface Handles serialization and deserialization of data.
     */
    protected SerializerInterface $serializer;

    /**
     * @var ModuleListInterface Provides module version information.
     */
    protected ModuleListInterface $moduleList;

    /**
     * @var AppState Provides the current application state.
     */
    protected AppState $appState;

    /**
     * @var RequestInterface Provides request information for current execution.
     */
    protected RequestInterface $request;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param SerializerInterface $serializer
     * @param AppState $appState
     * @param RequestInterface $request
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface   $encryptor,
        SerializerInterface  $serializer,
        AppState             $appState,
        RequestInterface     $request,
        ModuleListInterface  $moduleList
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->serializer = $serializer;
        $this->appState = $appState;
        $this->request = $request;
        $this->moduleList = $moduleList;
    }

    public function getModuleVersion(): ?string
    {
        if ($this->moduleVersion === null) {
            $moduleName = $this->getModuleName();
            $module = $this->moduleList->getOne($moduleName);
            $this->moduleVersion = $module['setup_version'] ?? null;
        }
        return $this->moduleVersion;
    }

    /**
     * Get the current setup version of the module.
     *
     * @return string|null The setup version or null if not found.
     */
    public function getSetupVersion(): ?string
    {
        if ($this->setupVersion === null) {
            $module = $this->moduleList->getOne($this->getModuleName());
            $this->setupVersion = $module['setup_version'] ?? null;
        }
        return $this->setupVersion;
    }

    /**
     * Get Current Module XML Version
     *
     * @return string|null
     */
    public function getModuleXmlVersion(): ?string
    {
        $module = $this->moduleList->getOne($this->getModuleName());
        return $module['setup_version'] ?? null;
    }

    /**
     * Get Current Module Name
     *
     * @return string
     */
    public function getModuleName(): string
    {
        if ($this->moduleName === null) {
            $reflection = new \ReflectionClass($this);
            $namespace = $reflection->getNamespaceName();
            $moduleNameParts = explode('\\', $namespace);
            $this->moduleName = $moduleNameParts[0] . '_' . $moduleNameParts[1];
        }
        return $this->moduleName;
    }

    /**
     * Get config flag value as boolean
     *
     * @param string $xmlPath
     * @param string|null $scope
     * @param string|null $scopeCode
     * @return bool
     */
    public function isSetFlag(string $xmlPath, ?string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, ?string $scopeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag($xmlPath, $scope);
    }

    /**
     * Get config flag value
     *
     * @param string $xmlPath
     * @param string|null $scope
     * @param string|null $scopeCode
     * @return mixed
     */
    public function getValue(string  $xmlPath,
                             ?string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                             ?string $scopeCode = null
    ): ?string {
        return $this->scopeConfig->getValue($xmlPath, $scope, $scopeCode);
    }

    /**
     * Get the decrypted configuration value.
     *
     * @param string $xmlPath The path to the encrypted config value.
     * @param string|null $scope The scope (default is global scope).
     * @return string|null The decrypted value or null if decryption fails.
     */
    public function getValueDecrypted(
        string  $xmlPath,
        ?string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        ?string $scopeCode = null
    ): ?string {
        $encryptedValue = $this->scopeConfig->getValue($xmlPath, $scope, $scopeCode);

        if ($encryptedValue) {
            try {
                // Attempt decryption
                $decryptedValue = $this->encryptor->decrypt((string)$encryptedValue);

                // Check if the decrypted value is a valid UTF-8 string
                if (mb_check_encoding($decryptedValue, 'UTF-8')) {
                    return $decryptedValue;
                } else {
                    // If not valid UTF-8, assume decryption failed
                    return null;
                }
            } catch (\Exception $e) {
                // Return null if decryption fails
                return null;
            }
        }

        // Return null if no encrypted value exists
        return null;
    }

    /**
     * Serialize data into string
     *
     * @param string|int|float|bool|array|null $data
     * @return string|bool
     * @throws \InvalidArgumentException
     */
    public function serialize($data): string
    {
        return $this->serializer->serialize($data);
    }

    /**
     * Unserialize string
     *
     * @param string $data
     * @return array|bool|float|int|string|null
     */
    public function deserialize(string $data)
    {
        return $this->serializer->unserialize($data);
    }

    /**
     * Get current Environment
     *
     * @return string
     */
    public function getCurrentArea(): string
    {
        if ($this->area === null) {
            try {
                $areaCode = $this->appState->getAreaCode();
            } catch (\Exception $e) {
                $areaCode = 'unknown';
            }

            switch ($areaCode) {
                case \Magento\Framework\App\Area::AREA_ADMINHTML:
                    $this->area = 'admin';
                    break;
                case \Magento\Framework\App\Area::AREA_FRONTEND:
                    $this->area = 'frontend';
                    break;
                case \Magento\Framework\App\Area::AREA_CRONTAB:
                    $this->area = 'cron';
                    break;
                default:
                    $this->area = 'unknown';
            }
        }
        return $this->area;
    }
}
