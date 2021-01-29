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
 * @package    Bss_PortoCustomSideBlock
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PortoCustomSideBlock\Block;

use Magento\Backend\Block\Template\Context;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Registry;

/**
 * Class Template
 * Bss\PortoCustomSideBlock\Block
 */
class Template extends \Smartwave\Porto\Block\Template
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Template constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        SessionFactory $customerSession,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context, $coreRegistry, $data);
    }

    /**
     * @return bool
     */
    public function customerIsLoggedIn(): bool
    {
        return $this->customerSession->create()->isLoggedIn();
    }
}
