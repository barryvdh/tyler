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
namespace Addify\PasswordProtected\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

class Delete extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'Addify_PasswordProtected::managepasswordprotected';
        
    protected $model;
    public function __construct(
        Action\Context $context,
        \Addify\PasswordProtected\Model\PasswordProtected $model
    ) {
        $this->model = $model;
        parent::__construct($context);
    }
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                $this->model->load($id);
                $title = $this->model->getTitle();
                $this->model->delete();
                // display success message
                $this->messageManager->addSuccess(__('The tab has been deleted.'));
                // go to grid
                $this->_eventManager->dispatch(
                    'adminhtml_extraproducttab_on_delete',
                    ['title' => $title, 'status' => 'success']
                );
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_extraproducttab_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a tab to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}