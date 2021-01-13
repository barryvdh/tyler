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

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class CountryOptions
 *
 * Bss\BrandRepresentative\Model\Config\Source
 */
class CountryOptions extends AbstractSource
{
    /**
     * @var DirectoryHelper
     */
    protected $directory;

    /**
     * @var Collection
     */
    protected $countryCollection;

    /**
     * CountryOptions constructor.
     * @param DirectoryHelper $directory
     * @param Collection $countryCollection
     */
    public function __construct(
        DirectoryHelper $directory,
        Collection $countryCollection
    ) {
        $this->directory = $directory;
        $this->countryCollection = $countryCollection;
    }

    /**
     * @return array
     */
    public function getAllOptions(): array
    {
        return $this->countryCollection->loadData()->toOptionArray(
            false
        );
    }
}
