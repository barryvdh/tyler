<?php
declare(strict_types=1);

namespace Bss\BrandRepresentative\Model\ResourceModel;

use Bss\BrandRepresentative\Model\MostViewed as Model;

/**
 * Class MostViewed
 */
class MostViewed extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE = 'bss_most_viewed_brands';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE, Model::ID);
    }
}
