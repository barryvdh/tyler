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

namespace Addify\PasswordProtected\Observer;

use Magento\Customer\Model\Session;

class CollectionObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $_ruleFactory;
    protected $_restrictcustomergroupHelper;
    protected $_storeManager;
    protected $_coreRegistry;
    protected $_productFactory;
    protected $_logger;

    protected $_customerSession;
    /**
     * using current context to add values and
     * avoid request refresh for cookie values
     * @var \Magento\Framework\App\Http\Context
     */
    protected $_httpContext;

    public function __construct(
        \Addify\PasswordProtected\Model\ResourceModel\PasswordProtected\CollectionFactory $collectionFactory,
        \Addify\PasswordProtected\Helper\HelperData $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Session $customerSession
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
        $this->_customerSession = $customerSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->coreSession->start();

        if (!$this->helper->isEnabledInFrontend()) {
            return;
        }
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


        $collection =$observer->getEvent()
                ->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', ['nin' => $finalExcludeProducts]);

        return $collection;
                
    }
}
