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
namespace Addify\PasswordProtected\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Session\SessionManagerInterface;

class HelperData extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ENABLED                        =   'passwordprotected/general/enabled';
    const XML_SEO_URL                             = 'passwordprotected/seosuffix/identifier';
    const XML_SEO_SUFFIX                          = 'passwordprotected/seosuffix/url_suffix';
    const XML_SEO_TITLE                           = 'passwordprotected/seosuffix/title';
    const XML_SEO_DESC                            = 'passwordprotected/pagesetting/description';
    const XML_SEO_BUTTON                          = 'passwordprotected/pagesetting/button';
    const XML_SEO_LABEL                           = 'passwordprotected/pagesetting/label';
    const XML_SEO_HEADING                         = 'passwordprotected/pagesetting/heading';
    const XML_SEO_HEADING1                        = 'passwordprotected/pagesetting/rightheading';
    const XML_SEO_GOOGLE                          = 'passwordprotected/seosuffix/googleenabled';
    const XML_PATH_HOME_PAGE                      = 'web/default/cms_home_page';



    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Addify\PasswordProtected\Model\ResourceModel\PasswordProtected\CollectionFactory $restrictOrderByCustomer,
        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $pageCollection
    )
    {
        parent::__construct($context);
        $this->restrictOrderByCustomer = $restrictOrderByCustomer;
        $this->pageCollection = $pageCollection;


    }
    public function isEnabledInFrontend()
    {
        $isEnabled = true;
        $enabled = $this->scopeConfig->getValue(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
        if ($enabled == null || $enabled == '0') {
            $isEnabled = false;
        }
        return $isEnabled;
    }
    public function minMessage()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MINMESSAGE, ScopeInterface::SCOPE_STORE);

    }
    public function maxMessage()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MAXMESSAGE, ScopeInterface::SCOPE_STORE);

    }
    public function getGoogleBot()
    {
        return $this->scopeConfig->getValue(self::XML_SEO_GOOGLE, ScopeInterface::SCOPE_STORE);

    }
    public function getCustomers($customerArr)
    {
        $newArray = array();
        $relatedProductsArr = json_decode($customerArr);
        if($relatedProductsArr):
        foreach ($relatedProductsArr as $key => $product)
        {
            $newArray[$key] = $key;
        }

        endif;

        return $relatedProducts  = implode(',', $newArray);

    }
    public function getRelatedProducts($relatedProductsArr)
    {
        $newArray = array();
        $count = 0;
        $relatedProductsArr = json_decode($relatedProductsArr);

        foreach ($relatedProductsArr as $key => $product)
        {
            $newArray[$count] = $key;
            $count++;
        }

        return $relatedProducts  = implode(',', $newArray);

    }
    public function getRelatedProductArray($id) //Return Related Products ID's Array w.r.t Tab ID
    {
        if(isset($id))
        {
            $relatedProducts = $this->restrictOrderByCustomer->create()
                ->addFieldToFilter('pp_id',$id)->getFirstItem();
            $relatedProducts  = $relatedProducts->getProductIds();
            $productArr = explode(',', $relatedProducts);

            if(!empty($productArr) && $productArr[0] != '')

                return $productArr;
            else
                return '';
        }
    }

    public function getSeoSetting()
    {
        return [
                'url' => $this->scopeConfig->getValue(self::XML_SEO_URL,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'suffix' => $this->scopeConfig->getValue(self::XML_SEO_SUFFIX,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'title' => $this->scopeConfig->getValue(self::XML_SEO_TITLE,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
        ];
    }
    public function getpageSetting()
    {
        return [
            'description' => $this->scopeConfig->getValue(self::XML_SEO_DESC,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'button' => $this->scopeConfig->getValue(self::XML_SEO_BUTTON,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'label' => $this->scopeConfig->getValue(self::XML_SEO_LABEL,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'heading' => $this->scopeConfig->getValue(self::XML_SEO_HEADING,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'rightheading' => $this->scopeConfig->getValue(self::XML_SEO_HEADING1,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
        ];
    }
    public function getHomepageId()
    {
        $homepage = $this->scopeConfig->getValue(self::XML_PATH_HOME_PAGE, ScopeInterface::SCOPE_STORE);
        return $this->pageCollection->create()->addFieldToFilter('identifier',$homepage)->getFirstItem()->getPageId();

    }

  
}