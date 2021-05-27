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
use Addify\PasswordProtected\Model\PasswordProtected;
use Addify\PasswordProtected\Model\Passwords;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Addify\PasswordProtected\Helper\HelperData;
use Addify\PasswordProtected\Model\ResourceModel\Passwords\CollectionFactory as PasswordsFactory;
use Addify\PasswordProtected\Model\ResourceModel\PasswordProtected\CollectionFactory as RestrictFactory;
 

class Save extends \Magento\Backend\App\Action
{
    
    const ADMIN_RESOURCE = 'Addify_PasswordProtected::managepasswordprotected';
    protected $dataProcessor;    
    protected $dataPersistor;
    protected $model;
    protected $helperData;
    protected $_storeManager;
    protected $restrictFactory;

    public function __construct(
        Action\Context $context,
        HelperData $helperData,
        PasswordsFactory $passwordsFactory,
        StoreManagerInterface $storeManager,
        PostDataProcessor $dataProcessor,
        PasswordProtected $model,
        Passwords $passwordModel,
        RestrictFactory $restrictFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    )
    {
        $this->passwordsFactory = $passwordsFactory;
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
        $this->model = $model;
        $this->passwordModel = $passwordModel;
        $this->helperData = $helperData;
        $this->_storeManager = $storeManager;
        $this->restrictFactory = $restrictFactory;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        parent::__construct($context);
    }

    public function execute()
    {
        
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {

           $this->dataProcessor->filter($data);


            $id = $data['pp_id'];
            $data['store'] = implode(',', $data['store']);
            $data['cms_ids'] = implode(',', $data['cms_ids']);
            if(is_array($data['data']['custom'])):
            $data['category_ids']=implode(',', $data['data']['custom']);
            endif;

            if(empty($id))
            {
                unset($data['pp_id']);


            }

            $this->model->setData($data);




                if(isset($data['tab_related_products']) )
                {
                    $relatedProductsArr = $data['tab_related_products'];
                    $relatedProducts = $this->helperData->getRelatedProducts($relatedProductsArr);
                    $this->model->setData('product_ids',$relatedProducts);
                }



            $this->_eventManager->dispatch(
                'passwordprotected_prepare_save',
                ['passwordprotected' => $this->model, 'request' => $this->getRequest()]
            );

            if (!$this->dataProcessor->validate($data)) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->model->getId(), '_current' => true]);
            }

            
            try {

                $this->model->save();
                $this->addPassword($this->model->getId(),$data);
                $types = array('collections');
                foreach ($types as $type) {
                    $this->cacheTypeList->cleanType($type);
                }
                foreach ($this->cacheFrontendPool as $cacheFrontend) {
                    $cacheFrontend->getBackend()->clean();
                }
                $this->messageManager->addSuccess(__('You saved the Record Successfully.'));
                $this->dataPersistor->clear('passwordprotected');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        ['id' => $this->model->getId(),
                         '_current' => true]
                    );
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __($e->getMessage()));
            }

            $this->dataPersistor->set('passwordprotected', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
    public function addPassword($id,$data){

        if(isset($data['password_holder'])):
            $passwords = $data['password_holder'];
            foreach ($passwords as $password):
                $collection = $this->passwordsFactory->create()->addFieldToFilter('password',$password['passwords']);
                if($collection->getData()):
                    $this->messageManager->addWarningMessage(__($password['passwords'].' Already Exist.'));

                else:

                $dataInput = array('password'=>$password['passwords'],'pp_id'=>$id);

                $this->passwordModel->setData($dataInput)->save();
                endif;
            endforeach;
        endif;

    }
}