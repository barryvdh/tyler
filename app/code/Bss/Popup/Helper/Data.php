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
 * @category   BSS
 * @package    Bss_Popup
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Popup\Helper;

use Magento\Store\Model\StoreManagerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Bss\Popup\Model\ResourceModel\Popup
     */
    protected $popupResourceModel;

    /**
     * Date Time
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * Scope Config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Popup Cookie
     *
     * @var \Bss\Popup\Model\PopupCookie
     */
    protected $popupCookie;

    /**
     * Session Manager
     *
     * @var \Magento\Framework\Session\SessionManager
     */
    protected $sessionManager;

    /**
     * Customer Session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Bss\Popup\Model\Form\FormKey
     */
    private $formKey;

    /**
     * Data constructor.
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Bss\Popup\Model\PopupCookie $popupCookie
     * @param \Magento\Framework\Session\SessionManagerFactory $sessionManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Bss\Popup\Model\ResourceModel\Popup $popupResourceModel
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Bss\Popup\Model\Form\FormKey $formKey
     */
    public function __construct(
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Bss\Popup\Model\PopupCookie $popupCookie,
        \Magento\Framework\Session\SessionManagerFactory $sessionManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Bss\Popup\Model\ResourceModel\Popup $popupResourceModel,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Bss\Popup\Model\Form\FormKey $formKey
    ) {
        $this->popupCookie = $popupCookie;
        $this->sessionManager = $sessionManager;
        $this->customerSession = $customerSession;
        $this->timezone = $timezone;
        $this->popupResourceModel = $popupResourceModel;
        $this->productMetadata = $productMetadata;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->formKey = $formKey;
        parent::__construct($context);
    }

    /**
     * Get data Popup
     *
     * @param string $handleList
     * @param int $storeId
     * @return bool|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @codingStandardsIgnoreStart
     */
    public function getPopup($handleList, $storeId)
    {
        $result = $this->popupResourceModel->getPopupByHandleList($handleList);
        $customerGroupId = $this->getCustomerGroupId();
        $popupId = '';
        foreach ($result as $row) {
            $checkStore = $this->checkStore($row['storeview'], $storeId);
            if (!$checkStore) {
                continue;
            }
            $checkCustomerGroup = $this->checkCustomerGroup($row['customer_group'], $customerGroupId);
            if (!$checkCustomerGroup) {
                continue;
            }
            $isPopupExpired = $this->isPopupExpired($row);
            if ($isPopupExpired) {
                continue;
            }
            if ($row['page_group'] == 'all_pages' && $row['entities'] != '') {
                $checkExclude = $this->checkExclude($handleList, $row['entities']);
                if (!$checkExclude) {
                    continue;
                }
            }
            $popupId = $row['popup_id'];
            break;
        }
        if ($popupId == '') {
            return false;
        }

        return $popupId;
    }
    // @codingStandardsIgnoreEnd

    /**
     * @param $stores
     * @param $storeId
     * @return bool
     */
    public function checkStore($stores, $storeId)
    {
        $stores = explode(",", $stores);
        if (in_array(0, $stores) || in_array($storeId, $stores)) {
            return true;
        }
        return false;
    }

    /**
     * @param $customerGroup
     * @param $customerGroupId
     * @return bool
     */
    public function checkCustomerGroup($customerGroup, $customerGroupId)
    {
        $customerGroup = explode(",", $customerGroup);
        if (in_array($customerGroupId, $customerGroup)) {
            return true;
        }
        return false;
    }

    /**
     * @param array $row
     * @return bool
     */
    public function isPopupExpired($row)
    {
        $fromStr = isset($row['display_from']) ? $row['display_from'] : false;
        $toStr = isset($row['display_to']) ? $row['display_to'] : false;
        $currentTime = $this->timezone->scopeTimeStamp();
        // Popup was not expire in cases
        // Not set $from and $to
        // Set $from and not set $to and $current >= $from
        // Not set $from and set $to and $current <= $to
        // Set $from and set $to and $from <= $current <= $to
        if (!$fromStr && !$toStr) {
            return false;
        } elseif ($fromStr && !$toStr) {
            $fromTime = strtotime($fromStr);
            return $fromTime > $currentTime;
        } elseif (!$fromStr && $toStr) {
            $toTime = strtotime($toStr);
            return $toTime < $currentTime;
        } elseif ($fromStr && $toStr) {
            $fromTime = strtotime($fromStr);
            $toTime = strtotime($toStr);
            return $fromTime > $currentTime || $toTime < $currentTime;
        } else {
            return true;
        }
    }

    /**
     * @param $handleList
     * @param $excludeHandle
     * @return bool
     */
    public function checkExclude($handleList, $excludeHandle)
    {
        $excludeHandle = explode(',', $excludeHandle);
        $currentPage = $handleList[0];
        if (count($handleList) > 1) {
            if (strpos($currentPage, 'product') !== false) {
                $currentPage = 'catalog_product_view';
            } else {
                $currentPage = 'catalog_category_view';
            }
        }
        if (in_array($currentPage, $excludeHandle)) {
            return false;
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function getSessionPageViewedByCustomer()
    {
        if ($this->sessionManager->create()->getPagesViewedByCustomer()) {
            $this->sessionManager->create()->setPagesViewedByCustomer($this->sessionManager->create()->getPagesViewedByCustomer() + 1);
        } else {
            $this->sessionManager->create()->setPagesViewedByCustomer(1);
        }

        return $this->sessionManager->create()->getPagesViewedByCustomer();
    }

    /**
     * @param $id
     */
    public function addPopupToSessionDisplayedPopup($id)
    {
        $displayedPopup = (!empty($this->sessionManager->create()->getDisplayedPopups())) ?
            $this->sessionManager->create()->getDisplayedPopups() : [0];

        if (!in_array($id, $displayedPopup)) {
            $displayedPopup[] = $id;
        }

        $this->sessionManager->create()->setDisplayedPopups($displayedPopup);
    }

    /**
     * @param $id
     * @return bool|int
     */
    public function popupNotInSession($id)
    {
        $displayed = true;
        $listPopupDisplayed = (!empty($this->sessionManager->create()->getDisplayedPopups())) ?
            $this->sessionManager->create()->getDisplayedPopups() : [0];
        if (in_array($id, $listPopupDisplayed)) {
            $displayed = 2;
        }
        return $displayed;
    }

    /**
     * @param $id
     * @param $duration
     * @throws \Exception
     */
    public function addPopupToCookie($id, $duration)
    {
        $cookieName = "popupCookie" . $id;
        if (empty($this->popupCookie->get($cookieName))) {
            $this->popupCookie->set($cookieName, "popup{$id}", $duration);
        }
    }

    /**
     * @param $id
     * @return string
     */
    public function getPopupCookie($id)
    {
        $cookieName = "popupCookie" . $id;
        return $this->popupCookie->get($cookieName);
    }

    /**
     * @param $popup
     * @return bool|int
     */
    public function popupIsAllowedDisplay($popup)
    {
        switch ($popup['frequently']) {
            case 1:
                return true;
            case 2:
                return $this->popupNotInSession($popup['popup_id']);
            case 3:
                if (!empty($this->getPopupCookie($popup['popup_id']))) {
                    return 0;
                }
                return true;
            default:
                return 0;
        }
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        if ($this->customerSession->create()->isLoggedIn()) {
            $groupId = $this->customerSession->create()->getCustomerGroupId();
            return $groupId;
        }
        return 0;
    }

    /**
     * @return bool
     */
    public function isAjaxCartBssEnable()
    {
        return $this->scopeConfig->isSetFlag(
            'ajaxcart/general/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getAddToCartSelector()
    {
        return $this->scopeConfig->getValue(
            'ajaxcart/general/selector',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function isM24Version()
    {
        return version_compare($this->productMetadata->getVersion(), '2.4.0', '>=');
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getUrlPreview()
    {
        $popupId = $this->request->getParam('popup_id', null);
        $formKey = $this->formKey->getFormKey();
        $base = $this->_getUrl('/');
        if (!empty($popupId)) {
            return trim($base, '/') . '&preview=1&id=' . $popupId . '&cid=' . $formKey;
        }
        return trim($base, '/') . '&preview=1&id=' . -1 . '&cid=' . $formKey;
    }
}
