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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Popup\Controller\Adminhtml\Popup;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $typeList;
    /**
     * @var \Bss\Popup\Helper\Layout
     */
    protected $layoutHelper;
    /**
     * Mass Action Filter
     *
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * Collection Factory
     *
     * @var \Bss\Popup\Model\ResourceModel\Popup\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * MassDelete constructor.
     * @param \Bss\Popup\Helper\Layout $layoutHelper
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Bss\Popup\Model\ResourceModel\Popup\CollectionFactory $collectionFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Cache\TypeListInterface $typeList,
        \Bss\Popup\Helper\Layout $layoutHelper,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Bss\Popup\Model\ResourceModel\Popup\CollectionFactory $collectionFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->typeList=$typeList;
        $this->layoutHelper = $layoutHelper;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }


    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        try {
            $delete = 0;
            foreach ($collection as $item) {
                /** @var \Bss\Popup\Model\Popup $item */
                $popupId = $item->getId();
                $this->removePopup($item);
                $this->layoutHelper->deleteOldLayoutUpadte($popupId);
                $this->layoutHelper->deleteOldLayout($popupId, []);
                $delete++;
            }

            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $delete));
            $this->typeList->invalidate(
                \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER
            );
            return $resultRedirect->setPath('*/*/');

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/');
        }
    }

    /**
     * Remove Popup
     *
     * @param \Bss\Popup\Model\Popup $popup
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Exception
     */
    protected function removePopup($popup)
    {
        $popup->delete();
    }

    /**
     * Check Rule
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Bss_Popup::delete");
    }

}
