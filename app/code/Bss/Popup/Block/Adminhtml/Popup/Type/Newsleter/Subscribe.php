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

namespace Bss\Popup\Block\Adminhtml\Popup\Type\Newsleter;

use Magento\Framework\View\Element\Template;
use Bss\Popup\Plugin\FrontendUrl;

/**
 * Class Subscribe handle logic template newsleter subscribe
 */
class Subscribe extends \Magento\Newsletter\Block\Subscribe
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
     * Returns action url for subcribe form
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->frontendUrl->getFrontendUrl()->getUrl('newsletter/subscriber/new', ['_secure' => true]);
    }
}
