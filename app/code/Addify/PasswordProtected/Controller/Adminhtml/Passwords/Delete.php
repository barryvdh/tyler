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
namespace Addify\PasswordProtected\Controller\Adminhtml\Passwords;

use Magento\Backend\App\Action;

class Delete extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'Addify_PasswordProtected::managepasswordprotected';
        
    protected $model;
    public function __construct(
        Action\Context $context,
        \Addify\PasswordProtected\Model\Passwords $model
    ) {
        $this->model = $model;
        parent::__construct($context);
    }
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('id');
        $ppId = $this->getRequest()->getParam('pp_id');

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                $this->model->load($id);
                $title = $this->model->getTitle();
                $this->model->delete();
                // display success message
                $this->messageManager->addSuccess(__('The password has been deleted.'));
                // go to grid
                $this->_eventManager->dispatch(
                    'adminhtml_passwordprotectedpassword_on_delete',
                    ['title' => $title, 'status' => 'success']
                );
                return $resultRedirect->setPath('*/*/', ['pp_id' => $ppId]);
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_passwordprotectedpassword_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/', ['pp_id' => $ppId]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a password to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/', ['pp_id' => $ppId]);
    }
}