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
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Popup\Controller\Adminhtml\Template;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\LayoutInterface;

/**
 * Class Type get template by type template
 */
class Type extends Action
{
    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param LayoutInterface $layout
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        LayoutInterface $layout,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->layout = $layout;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Get data form phtml by code type template
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $codeTypeTemplate = $this->getRequest()->getParam('type_template');
        if ($codeTypeTemplate) {
            $typeTemplate = [
                "none",
                "template_contact_form",
                "template_age_verification",
                "template_newsletter",
                "product_listing",
                "social_sharing"
            ];
            $blockTemplate = [
                \Magento\Framework\View\Element\Template::class,
                \Bss\Popup\Block\Adminhtml\Popup\Type\ContactForm::class,
                \Magento\Framework\View\Element\Template::class,
                \Bss\Popup\Block\Adminhtml\Popup\Type\Newsleter\Subscribe::class,
                \Magento\Framework\View\Element\Template::class,
                \Magento\Framework\View\Element\Template::class,
            ];
            $template = $typeTemplate[$codeTypeTemplate];
            $block = $this->layout->createBlock($blockTemplate[$codeTypeTemplate])
                ->setTemplate("Bss_Popup::type/" . $template . ".phtml");
            return $resultJson->setData($block->toHtml());
        } else {
            return $resultJson->setData('');
        }
    }
}
