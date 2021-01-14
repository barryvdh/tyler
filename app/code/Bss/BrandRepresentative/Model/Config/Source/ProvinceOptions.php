<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_BrandRepresentative
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\BrandRepresentative\Model\Config\Source;

use Magento\Directory\Model\Country;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Directory\Model\ResourceModel\Country\Collection;

/**
 * Class ProvinceOptions
 * Bss\BrandRepresentative\Model\Config\Source
 */
class ProvinceOptions extends AbstractSource
{
    /**
     * @var Collection
     */
    protected $country;

    /**
     * @var Country
     */
    protected $countryModel;

    public function __construct(
        Collection $countryCollection,
        Country $country
    ) {
        $this->country = $countryCollection;
        $this->countryModel = $country;
    }

    /**
     * @inheritdoc
     */
    public function getAllOptions(): array
    {
        $regions = [];
        $countryCollection = $this->country->loadData();
        foreach ($countryCollection as $country) {
            $regionCollection = $this->countryModel->loadByCode($country["country_id"])->getRegions();
            $regionOptions = $regionCollection->loadData()->toOptionArray();
            if ($regionOptions) {
                unset($regionOptions[0]);
                foreach ($regionOptions as $region) {
                    $regions[] = $region;
                }
            }
        }
        return $regions;
    }
}
