<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Block\Customer\Account;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template;

/**
 * Class Edit
 */
class Edit extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * Edit constructor.
     *
     * @param Template\Context $context
     * @param CustomerSession $customerSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * Logged in is sub-user
     *
     * @return bool
     */
    public function isSubUser(): bool
    {
        if ($this->customerSession->getSubUser()) {
            return true;
        }
        return false;
    }
}
