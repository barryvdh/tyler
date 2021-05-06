<?php
/**
 * Password Protected
 *
 * @category    Addify
 * @package     Addify_PasswordProtected
 * @author      Addify
 * @Email       addifypro@gmail.com
 *
 */
namespace Addify\PasswordProtected\Plugin;

class ProductListing
{
    protected $helper;
    protected $collection;

    public function __construct(
        \Addify\PasswordProtected\Model\ResourceModel\PasswordProtected\CollectionFactory $collectionFactory,
        \Addify\PasswordProtected\Helper\HelperData $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {

        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        $this->coreSession = $coreSession;
        $this->_storeManager = $storeManager;
        $this->_currentStoreView = $this->_storeManager->getStore();
        $this->_coreRegistry = $coreRegistry;
        $this->_productFactory = $productFactory;
        $this->_logger = $logger;
        $this->_messageManager = $messageManager;
    }

    public function aroundGetProductCollection(\Magento\Catalog\Model\Layer $subject,
                                               \Closure $proceed)
    {

        if (!$this->helper->isEnabledInFrontend()) {
            return;
        }
        $this->coreSession->start();

        $allCollection =$this->collectionFactory->create()->addFieldToFilter('is_active',1);

        $notRestrictData = $this->coreSession->getPasswordProductUpdatedData();

        $productIds = array();
        $product = array();
        if($notRestrictData):
            $productIds = $notRestrictData['product'];

        endif;
        foreach($allCollection as $collect):
            $products =explode(',',$collect->getProductIds());
            foreach ($products as $pro):
                if(!in_array($pro,$productIds)):
                    $product[]= $pro;
                endif;
            endforeach;
        endforeach;



        $finalExcludeProducts = array_values(array_unique($product));
        $result = $proceed();
        $result
            ->addFieldToFilter('entity_id', ['nin' => $finalExcludeProducts]);

        return $result;

    }


}