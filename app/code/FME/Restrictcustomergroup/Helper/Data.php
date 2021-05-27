<?php
/**
 * Class for Restrictcustomergroup Helper Data
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */

namespace FME\Restrictcustomergroup\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Cms\Model\PageFactory;
use Magento\Customer\Model\GroupFactory;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

class Data extends AbstractHelper
{

    protected $_customerGroupFactory;
    protected $_resource;
    private $remoteAddress;

    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    protected $_directoryList;
    protected $httpHeader;

    /**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;
    protected $_customerSession;

    const XML_PATH_ENABLED = 'restrictcustomergroup/general/enable_in_frontend';
    const XML_PATH_EXCLUDE_IP = 'restrictcustomergroup/general/exclude_ip';

    public function __construct(
        Context $context,
        DirectoryList $directoryList,
        PageFactory $pageFactory,
        GroupFactory $groupFactory,
        \Magento\Framework\HTTP\Header $httpHeader,
        RemoteAddress $remoteAddress,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context);
        $this->_resource = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('\Magento\Framework\App\ResourceConnection');
        $this->_logger = $context->getLogger();
        $this->_directoryList = $directoryList;
        $this->_pageFactory = $pageFactory;
        $this->_customerGroupFactory = $groupFactory;
        $this->httpHeader = $httpHeader;
        $this->remoteAddress = $remoteAddress;
        $this->_customerSession = $customerSession;
    }

    public function getCustomerGroupId()
    {
      return $this->_customerSession->getCustomer()->getGroupId();
    }

    public function getRemoteIPAddress()
    {
      return $this->remoteAddress->getRemoteAddress();
    }

    /**
     *
     * check the module is enabled, frontend
     *
     * @param mix $store
     * @return string
     */
    public function isEnabledInFrontend($store = null)
    {
        $isEnabled = true;
        $enabled = $this->scopeConfig->getValue(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
        if ($enabled == null || $enabled == '0') {
            $isEnabled = false;
        }
        return $isEnabled;
    }

    public function getExcludedIP()
    {
        $excludedip = $this->scopeConfig->getValue(self::XML_PATH_EXCLUDE_IP, ScopeInterface::SCOPE_STORE);
        $excludediparray = [];
        if (!empty($excludedip))
        {
          if (strpos($excludedip, ',') !== false) {
              $excludediparray = explode(',', $excludedip);
          }
          else {
            $excludediparray[] = $excludedip;
          }
        }
        return $excludediparray;
    }

    public function isWebCrawler($request)
    {
        $userAgent = $this->httpHeader->getHttpUserAgent();
        if (preg_match("/Googlebot/", $userAgent)) {
            $remoteAddress = new \Magento\Framework\Http\PhpEnvironment\RemoteAddress($request);

            //hostname is assigned to $hostname
            $hostname = $remoteAddress->getRemoteHost();

            if (preg_match("/googlebot.com/", $hostname)) {
                // returns true if googlebot.com is found in hostname
                return true;
            }
        }
        return false;
    }

    /**
     * Get 404 file not found url
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function _getNotFoundUrl($route = '', $params = ['_direct' => 'core/index/notFound'])
    {
        return $this->_getUrl($route, $params);
    }

    public function getViewFileUrl()
    {
        return $this->_urlBuilder
                ->getBaseUrl(
                    ['_type' => \Magento\Framework\UrlInterface::URL_TYPE_STATIC,
                    '_current' => true]
                ) . 'adminhtml/Magento/backend/en_US/FME/Restrictcustomergroup/view/adminhtml/web/restrictcustomergroup/';
    }

    public function getCmsPageModel()
    {
        return $this->_pageFactory->create();
    }

    public function getCustomerGroupModel()
    {
        return $this->_customerGroupFactory->create();
    }
}
