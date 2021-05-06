<?php
/**
 * Class for Restrictcustomergroup Restricted
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Controller\Index;

class Restricted extends \Magento\Framework\App\Action\Action
{
    protected $_transportBuilder;
    protected $inlineTranslation;
    protected $request;
    protected $scopeConfig;
    protected $storeManager;
    protected $_customerSession;
    protected $resultPageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Request\Http $request
    ) {
        parent::__construct($context);
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->_customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
      $resultPage = $this->resultPageFactory->create();
      return $resultPage;
    }
}
