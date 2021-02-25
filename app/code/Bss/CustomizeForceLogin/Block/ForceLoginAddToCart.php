<?php
declare(strict_types=1);
namespace Bss\CustomizeForceLogin\Block;

use Bss\CustomizeForceLogin\Helper\Data;
use Magento\Framework\View\Element\Template;

/**
 * Class ForceLoginAddToCart
 */
class ForceLoginAddToCart extends Template
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * ForceLoginAddToCart constructor.
     *
     * @param Data $helper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Data $helper,
        Template\Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get block template
     *
     * @return string
     */
    public function getTemplate()
    {
        return "Bss_CustomizeForceLogin::force_login_add_to_cart.phtml";
    }

    /**
     * Need remove add to cart button
     *
     * @return bool
     */
    public function isRemoveAddToCartBtn(): bool
    {
        return $this->helper->cantAddToCart();
    }
}
