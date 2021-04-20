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

namespace Bss\Popup\Block\Adminhtml\Popup\Type;

use Magento\Framework\View\Element\Template;
use Bss\Popup\Plugin\FrontendUrl;

/**
 * Class ContactForm handle logic template contact form
 */
class ContactForm extends \Magento\Contact\Block\ContactForm
{
    /**
     * @var FrontendUrl
     */
    protected $frontendUrl;

    /**
     * ContactForm constructor.
     * @param FrontendUrl $frontendUrl
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        FrontendUrl $frontendUrl,
        Template\Context $context,
        array $data = []
    ) {
        $this->frontendUrl=$frontendUrl;
        parent::__construct($context, $data);
    }

    /**
     * Returns action url for contact form
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->frontendUrl->getFrontendUrl()->getUrl('contact/index/post', ['_secure' => true]);
    }
}
