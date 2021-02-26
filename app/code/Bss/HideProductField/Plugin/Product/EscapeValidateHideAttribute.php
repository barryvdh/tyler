<?php
declare(strict_types=1);

namespace Bss\HideProductField\Plugin\Product;

use Bss\HideProductField\Helper\Data;

/**
 * Class EscapeValidateHideAttribute
 * No validate hide attributes to avoid error message
 */
class EscapeValidateHideAttribute
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var Data
     */
    private $helper;

    /**
     * EscapeValidateHideAttribute constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param Data $helper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * No validate hide attributes to avoid error message
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Validate $subject
     * @param \Magento\Framework\Controller\Result\Json $result
     * @return \Magento\Framework\Controller\Result\Json
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        \Magento\Catalog\Controller\Adminhtml\Product\Validate $subject,
        \Magento\Framework\Controller\Result\Json $result
    ) {
        try {
            if (!$this->helper->isEnable()) {
                return $result;
            }

            $errorAttribute = $result->getData();
            if ($errorAttribute) {
                $hideAttributes = $this->helper->getAdditionalAttributeConfig();
                if (in_array($errorAttribute["attribute"], explode(",", $hideAttributes))) {
                    $response = new \Magento\Framework\DataObject();
                    $response->setError(false);
                    $result->setData($response);
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $result;
    }
}
