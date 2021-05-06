<?php
/**
 * Class for Restrictcustomergroup Delete
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Controller\Adminhtml\Rule;

class Delete extends \Magento\Backend\App\Action
{
    protected $_restrictcustomergroupFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \FME\Restrictcustomergroup\Model\RuleFactory $ruleFactory
    ) {

        parent::__construct($context);
        $this->_restrictcustomergroupFactory = $ruleFactory;
    }
    /**
     * @return void
     */
    public function execute()
    {
        $ruleId = (int) $this->getRequest()->getParam('rule_id');
        if ($ruleId) {
            /** @var $newsModel \Fme\News\Model\News */
            $restrictcustomergroupModel = $this->_restrictcustomergroupFactory->create();
            $restrictcustomergroupModel->load($ruleId);

            // Check this news exists or not
            if (!$restrictcustomergroupModel->getId()) {
                $this->messageManager->addError(__('This rule no longer exists.'));
            } else {
                try {
                    // Delete news
                    $restrictcustomergroupModel->delete();
                    $this->messageManager->addSuccess(__('The rule has been deleted.'));

                    // Redirect to grid page
                    $this->_redirect('*/*/');
                    return;
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                    $this->_redirect('*/*/edit', ['rule_id' => $restrictcustomergroupModel->getId()]);
                }
            }
        }
    }
}
