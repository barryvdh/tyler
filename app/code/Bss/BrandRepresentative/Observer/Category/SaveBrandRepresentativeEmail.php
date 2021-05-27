<?php
declare(strict_types=1);

namespace Bss\BrandRepresentative\Observer\Category;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

/**
 * Class SaveBrandRepresentativeEmail - Save the brand Representative email data
 */
class SaveBrandRepresentativeEmail implements ObserverInterface
{
    const BRAND_REPRESENTATIVE_EMAIL_REQUEST_FIELD = 'bss_brand_representative_email';
    const USE_COMPANY_CATEGORY_CONFIG_REQUEST_FIELD = 'use_company_configuration';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * SaveBrandRepresentativeEmail constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param RequestInterface $request
     * @param SerializerInterface $serializer
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        RequestInterface $request,
        SerializerInterface $serializer,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->serializer = $serializer;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        try {
            $brandRepresentativeEmail = $this->request->getParam(
                self::BRAND_REPRESENTATIVE_EMAIL_REQUEST_FIELD
            );
            $useCompanyConfig = $this->request->getParam(self::USE_COMPANY_CATEGORY_CONFIG_REQUEST_FIELD);
            $category = $observer->getData('data_object');

            if (!$category) {
                return $this;
            }
            $brandRepresentativeEmailData = [
                self::USE_COMPANY_CATEGORY_CONFIG_REQUEST_FIELD => $useCompanyConfig
            ];

            if ($brandRepresentativeEmail && $useCompanyConfig == 0) {
                $brandRepresentativeEmailData = array_merge($brandRepresentativeEmail, $brandRepresentativeEmailData);
            }

            if ($brandRepresentativeEmailData) {
                $brandRepresentativeEmailData = $this->serializer->serialize($brandRepresentativeEmailData);
                $category->setData(
                    self::BRAND_REPRESENTATIVE_EMAIL_REQUEST_FIELD,
                    $brandRepresentativeEmailData
                );

                if (!$category->getData('has_saved_brand_representative_email')) {
                    $category->setData(
                        'has_saved_brand_representative_email',
                        true
                    );
                    $this->categoryRepository->save($category);
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $this;
    }
}
