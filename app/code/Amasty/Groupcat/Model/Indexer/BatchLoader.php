<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

declare(strict_types=1);

namespace Amasty\Groupcat\Model\Indexer;

use Magento\Framework\Data\Collection;

class BatchLoader
{
    const BATCH_SIZE = 1000;

    public function batchLoad(Collection $collection, int $batchSize = self::BATCH_SIZE): \Generator
    {
        $currentPage = 1;
        $collection->setPageSize($batchSize);
        $collection->setCurPage($currentPage);
        $totalPagesCount = $collection->getLastPageNumber();

        while ($currentPage <= $totalPagesCount) {
            $collection->clear();
            $collection->setCurPage($currentPage);

            foreach ($collection->getItems() as $item) {
                yield $item;
            }

            $currentPage++;
        }
    }
}
