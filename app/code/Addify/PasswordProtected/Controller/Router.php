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
namespace Addify\PasswordProtected\Controller;

class Router implements \Magento\Framework\App\RouterInterface
{
    
    protected $actionFactory;

    protected $_eventManager;

    protected $_storeManager;

    
    protected $shopByBrand;

    protected $_appState;

    protected $_url;

    protected $_response;
    protected $helper;

    
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,
        \Addify\PasswordProtected\Helper\HelperData $ppHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->actionFactory = $actionFactory;
        $this->_eventManager = $eventManager;
        $this->_url = $url;
        $this->ppHelper = $ppHelper;
        $this->_storeManager = $storeManager;
        $this->_response = $response;
    }

    /**
     * Validate and Match Faqs main-page, detail page and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {

        if($this->ppHelper->isEnabledInFrontend()):

            $moduleSeoSetting = $this->ppHelper->getSeoSetting();
            $identifier = trim($request->getPathInfo(), '/');
            $oldIdentifier=$identifier;
            $identifier = str_replace($moduleSeoSetting['suffix'], '', $identifier);
            
            $condition = new \Magento\Framework\DataObject(['identifier' => $identifier, 'continue' => true]);
            $this->_eventManager->dispatch(
                'fme_shopbybrand_controller_router_match_before',
                ['router' => $this, 'condition' => $condition]
            );
            $identifier = $condition->getIdentifier();
            
            if ($condition->getRedirectUrl()) {
                $this->_response->setRedirect($condition->getRedirectUrl());
                $request->setDispatched(true);
                return $this->actionFactory->create('Magento\Framework\App\Action\Redirect');
            }

            if (!$condition->getContinue()) {
                return null;
            }
            
            /*check identifier against shopbybrand-configuration main shopbybrand idendifier */
            
            $mainIdentifier = $moduleSeoSetting['url'];
             
            if ($mainIdentifier == $identifier) {
                $request->setModuleName('passwordprotected')->setControllerName('index')->setActionName('index');
                $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $oldIdentifier);
                return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
            }
            
            endif;
            
            

    }
}
