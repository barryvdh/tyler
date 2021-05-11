<?php
declare(strict_types=1);
namespace Bss\CustomizeCompanyAccount\Plugin\Controller\Customer\Adminhtml\Index;

use Magento\Customer\Controller\Adminhtml\Index\InlineEdit as BePlugged;

/**
 * Class InlineEdit
 * Prevent edit sub-user inline
 */
class InlineEdit
{
    /**
     * Prevent edit sub-user inline
     *
     * @param BePlugged $subject
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function beforeExecute(
        BePlugged $subject
    ) {
        $request = $subject->getRequest();

        $params = $request->getParams();
        if (isset($params['items'])) {
            foreach ($params['items'] as $cId => $param) {
                if (preg_match('/sub-/', $cId)) {
                    unset($params['items'][$cId]);
                }
            }
        }

        $subject->getRequest()->setParams($params);
    }
}
