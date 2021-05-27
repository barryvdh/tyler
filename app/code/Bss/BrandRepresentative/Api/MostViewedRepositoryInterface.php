<?php
declare(strict_types=1);
namespace Bss\BrandRepresentative\Api;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class MostViewedRepositoryInterface
 */
interface MostViewedRepositoryInterface
{
    /**
     * Plus more number of visit
     *
     * @param int $categoryId
     * @param int $entityType
     * @param int $number
     * @return bool
     * @throws LocalizedException
     */
    public function addVisitNumber($categoryId, $entityType, $number = 1);

    /**
     * Get most view detail by id or category id
     *
     * @param int $id
     * @param bool $byCategory
     * @return \Bss\BrandRepresentative\Api\Data\MostViewedInterface
     * @throws LocalizedException
     */
    public function get($id, $byCategory = true);

    /**
     * @param \Bss\BrandRepresentative\Api\Data\MostViewedInterface $mostViewed
     * @return \Bss\BrandRepresentative\Api\Data\MostViewedInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Bss\BrandRepresentative\Api\Data\MostViewedInterface $mostViewed);
}
