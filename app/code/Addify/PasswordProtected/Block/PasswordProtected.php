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
namespace Addify\PasswordProtected\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\ObjectManagerInterface;

class PasswordProtected extends Template
{

    protected $scopeConfig;
    protected $collectionFactory;
    protected $objectManager;
    public    $helper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Addify\PasswordProtected\Helper\HelperData $helper,
        ObjectManagerInterface $objectManager,
        \Zend_Filter_Interface $templateProcessor
    ) {

        $this->objectManager = $objectManager;
        $this->helper = $helper;
        $this->templateProcessor = $templateProcessor;

        parent::__construct($context);
    }
    public  function submiturl()
    {
        return $this->getUrl('passwordprotected/index/post');
    }
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $seo =$this->helper->getSeoSetting();
        if ($this->helper->isEnabledInFrontend()) {
            $this->pageConfig->getTitle()->set($seo['title']);

            return $this;
        }
    }
    public function filterOutputHtml($string)
    {
        return $this->templateProcessor->filter($string);
    }



}
