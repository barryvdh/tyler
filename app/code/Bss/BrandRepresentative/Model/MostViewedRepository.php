<?php
declare(strict_types=1);
namespace Bss\BrandRepresentative\Model;

use Bss\BrandRepresentative\Api\Data\MostViewedInterface;
use Bss\BrandRepresentative\Api\MostViewedRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MostViewedRepository
 */
class MostViewedRepository implements MostViewedRepositoryInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var ResourceModel\MostViewed
     */
    private $mostViewedResource;

    /**
     * @var MostViewedFactory
     */
    private $mostViewedFactory;

    /**
     * MostViewedRepository constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param ResourceModel\MostViewed $mostViewedResource
     * @param MostViewedFactory $mostViewedFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Bss\BrandRepresentative\Model\ResourceModel\MostViewed $mostViewedResource,
        \Bss\BrandRepresentative\Model\MostViewedFactory $mostViewedFactory
    ) {
        $this->logger = $logger;
        $this->mostViewedResource = $mostViewedResource;
        $this->mostViewedFactory = $mostViewedFactory;
    }

    /**
     * @inheritDoc
     */
    public function addVisitNumber($entityId, $entityType, $number = 1)
    {
        try {
            $mostViewed = $this->get($entityId);

            if ($mostViewed->getId()) {
                $number += (int) $mostViewed->getTraffic();
            }

            $mostViewed->setEntityId($entityId);
            $mostViewed->setTraffic($number);
            $mostViewed->setEntityType($entityType);
            $this->save($mostViewed);

            return true;
        } catch (CouldNotSaveException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(__("Something went wrong. Please review the log!"));
        }
    }

    /**
     * @inheritDoc
     */
    public function get($id, $byEntity = true)
    {
        try {
            $mostViewed = $this->mostViewedFactory->create();
            $loadField = MostViewedInterface::ENTITY_ID;
            if (!$byEntity) {
                $loadField = MostViewedInterface::ID;
            }
            $this->mostViewedResource->load($mostViewed, $id, $loadField);

            return $mostViewed;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(__("Something went wrong. Please review the log!"));
        }
    }

    /**
     * @inheritDoc
     */
    public function save(\Bss\BrandRepresentative\Api\Data\MostViewedInterface $mostViewed)
    {
        try {
            $this->mostViewedResource->save($mostViewed);

            return $mostViewed;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotSaveException(__("Could not save the site traffic. Please review the log!"));
        }
    }
}
