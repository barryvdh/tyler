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
 * @category BSS
 * @package Bss_CustomerAttributes
 * @author Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomerAttributes\Controller\Index;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Viewfile
 *
 * @package Bss\CustomerAttributes\Controller\Index
 */
class GetValue extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    private $helper;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Json $json
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $helper
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        Json $json,
        \Bss\CustomerAttributes\Helper\Customerattribute $helper,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->json = $json;
        $this->helper = $helper;
        $this->attributeRepository = $attributeRepository;

    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Json|ResultInterface
     */
    public function execute()
    {
        $resultJson = '';
        $customerAttributes = $this->getRequest()->getParam('data');
        if ($customerAttributes) {
            $customerAttributes = $this->json->unserialize($customerAttributes);
            $resultJson = $this->getValue($customerAttributes);
        }

        $result = $this->resultJsonFactory->create();
        $result->setData(['convertValue' => $resultJson]);
        return $result;
        }

    /**
     * @param $customerAddressAttribute
     * @return false
     */
        private function getValue($customerAddressAttribute)
        {
        $value = '';
        if ($customerAddressAttribute['attribute_code']){
            $attributeCode = trim($customerAddressAttribute['attribute_code'],'[]');
            try {
                if (!$this->helper->isCustomAddressAttribute($attributeCode)){
                    return false;
                }
                $addressAttribute = $this->attributeRepository
                    ->get('customer_address', $attributeCode);
                $addressValue = $this->helper->getValueAddressAttributeForOrder(
                    $addressAttribute,
                    $customerAddressAttribute['value']
                );
                $value = [
                    'attribute_code' => $attributeCode,
                    'value' => $customerAddressAttribute['value'],
                    'label' => $addressValue
                ];
            } catch (\Exception $e){
                return false;
            }
        }
        return $this->json->serialize($value);
        }

    }
