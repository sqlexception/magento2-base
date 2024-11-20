<?php declare(strict_types=1);

namespace SqlException\Base\Model;

use Magento\Framework\App\Area;
use Magento\Framework\App\AreaInterface;
use Magento\Framework\App\AreaList;
use Magento\Store\Model\App\Emulation;

class StoreEmulation
{
    /**
     * @var AreaList|null
     */
    private ?AreaList $areaList;

    /**
     * @var Emulation|null
     */
    private ?Emulation $emulation;

    /**
     * @param Emulation $emulation
     * @param AreaList $areaList
     */
    public function __construct(Emulation $emulation, AreaList $areaList)
    {
        $this->emulation = $emulation;
        $this->areaList = $areaList;
    }

    public function runInStore(int $storeId, callable $proceed)
    {
        try {
            $this->emulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);

            $area = $this->areaList->getArea(Area::AREA_FRONTEND);
            $area->load(AreaInterface::PART_TRANSLATE);

            $proceed();
        } finally {
            $this->emulation->stopEnvironmentEmulation();
        }
    }
}
