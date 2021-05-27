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
namespace Addify\PasswordProtected\Observer;

use Magento\Framework\Event\ObserverInterface;

class PasswordProtected implements ObserverInterface
{

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;


    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    public function __construct(
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\Session $catalogSession,
        \Addify\PasswordProtected\Model\ResourceModel\PasswordProtected\CollectionFactory $collectionModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Addify\PasswordProtected\Helper\HelperData $helperData
    ) {
        $this->_actionFlag = $actionFlag;
        $this->redirect = $redirect;
        $this->coreSession = $coreSession;
        $this->catalogSession = $catalogSession;
        $this->request = $request;
        $this->helper = $helperData;
        $this->collectionModel = $collectionModel;
        $this->storeManager = $storeManager;




    }


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
         $controller =$this->request->getControllerName();
         $action = $this->request->getActionName();
         $route = $this->request->getRouteName();
         $this->coreSession->start();
         $seoSetting = $this->helper->getSeoSetting();
        if(!$this->helper->isEnabledInFrontend()) {

            return $this;
        }
        if($_SERVER['HTTP_USER_AGENT'] == 'googlebot' && $this->helper->getGoogleBot()) {

            return $this;
        }
        $allCollection =$this->collectionModel->create()->addFieldToFilter('is_active',1);
            $product = array();
            $categories = array();
            $cms = array();

            foreach($allCollection as $collect):
            $products =explode(',',$collect->getProductIds());
            foreach ($products as $pro):
                $product[]= $pro;
                endforeach;
            $category =explode(',',$collect->getCategoryIds());
            foreach ($category as $cate):
                $categories[]= $cate;
                endforeach;
            $cmsIds =explode(',',$collect->getCmsIds());
            foreach ($cmsIds as $cmsId):
                $cms[]= $cmsId;
                endforeach;
            endforeach;
            $passwordProductData = array('product'=>$product,'category'=>$categories,'cms'=>$cms);
            $this->coreSession->setPasswordProductData($passwordProductData);

        if($controller=='category' && $route=='catalog' && $action=='view'):
            $categoryId = $this->request->getParam('id');

                $notRestrictData = $this->coreSession->getPasswordProductUpdatedData();
            if($notRestrictData):

                if(in_array($categoryId,$notRestrictData['category'])):
                    return $this;
                endif;
            endif;

            if(in_array($categoryId,$categories)):

                $controller = $observer->getControllerAction();
                $this->coreSession->unsCompareRedirect();
                $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $ppRedirect = array('redirect'=>true,'type'=>'category','category_id'=>$categoryId,'redirect_url'=>$this->storeManager->getStore()->getCurrentUrl(false));
                $this->coreSession->setPasswordProductRedirect($ppRedirect);
                $this->redirect->redirect($controller->getResponse(), $seoSetting['url'].$seoSetting['suffix']);
                return $this;
            endif;

        endif;

        if($controller=='product' && $route=='catalog' && $action=='view'):


            $productId = $this->request->getParam('id');
            $notRestrictData = $this->coreSession->getPasswordProductUpdatedData();
            if($notRestrictData):

                if(in_array($productId,$notRestrictData['product'])):
                    return $this;
                endif;
            endif;
            if(in_array($productId,$product)):

                $controller = $observer->getControllerAction();
                $this->coreSession->unsCompareRedirect();
                $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $ppRedirect = array('redirect'=>true,'type'=>'product','product_id'=>$productId,'redirect_url'=>$this->storeManager->getStore()->getCurrentUrl(false));
                $this->coreSession->setPasswordProductRedirect($ppRedirect);
                $this->redirect->redirect($controller->getResponse(), $seoSetting['url'].$seoSetting['suffix']);
                return $this;
            endif;

        endif;
        if($controller=='index' && $route=='cms' && $action=='index'):

              $cmsId =   $this->helper->getHomepageId();
            $notRestrictData = $this->coreSession->getPasswordProductUpdatedData();
            if($notRestrictData):

                if(in_array($cmsId,$notRestrictData['cms'])):
                    return $this;
                endif;
            endif;
            if(in_array($cmsId,$cms)):

                $controller = $observer->getControllerAction();
                $this->coreSession->unsCompareRedirect();
                $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $ppRedirect = array('redirect'=>true,'type'=>'cms','cms_id'=>$cmsId,'redirect_url'=>$this->storeManager->getStore()->getCurrentUrl(false));
                $this->coreSession->setPasswordProductRedirect($ppRedirect);
                $this->redirect->redirect($controller->getResponse(), $seoSetting['url'].$seoSetting['suffix']);
                return $this;
            endif;

        endif;
        if($controller=='page' && $route=='cms' && $action=='view'):

            $cmsId = $this->request->getParam('page_id');
            $notRestrictData = $this->coreSession->getPasswordProductUpdatedData();
            if($notRestrictData):

                if(in_array($cmsId,$notRestrictData['cms'])):
                    return $this;
                endif;
            endif;
            if(in_array($cmsId,$categories)):

                $controller = $observer->getControllerAction();
                $this->coreSession->unsCompareRedirect();
                $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $ppRedirect = array('redirect'=>true,'type'=>'cms','cms_id'=>$cmsId,'redirect_url'=>$this->storeManager->getStore()->getCurrentUrl(false));
                $this->coreSession->setPasswordProductRedirect($ppRedirect);
                $this->redirect->redirect($controller->getResponse(), $seoSetting['url'].$seoSetting['suffix']);
                return $this;
            endif;

        endif;



        return $this;
    }
}
