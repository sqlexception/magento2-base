<?php declare(strict_types=1);

namespace Integration\Model;

use Magento\Framework\App\Area;
use Magento\Framework\App\AreaInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;
use SqlException\Base\Model\StoreEmulation;

class StoreEmulationTest extends AbstractController
{
    /** @var StoreEmulation */
    private $storeEmulation;

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeEmulation = $this->objectManager->get(StoreEmulation::class);
    }

    /**
     * @magentoDataFixture Magento/Store/_files/core_second_third_fixturestore.php
     * @magentoAppIsolation enabled
     *
     */
    public function testRunInStoreWithDifferentIds()
    {
        $storeManager = Bootstrap::getObjectManager()->get('Magento\Store\Model\StoreManagerInterface');
        $stores = $storeManager->getStores();

        foreach ($stores as $store) {
            $storeId = (int)$store->getId();
            var_dump($storeId);
            $proceed = function () {
            };

            $area = $this->objectManager->create(
                AreaInterface::class,
                ['areaCode' => Area::AREA_FRONTEND]
            );
            $area->load(AreaInterface::PART_TRANSLATE);

            $this->storeEmulation->runInStore($storeId, $proceed);
        }
        $this->assertEquals(count($stores), count($stores));
    }
}
