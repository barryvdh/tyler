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
namespace Addify\PasswordProtected\Controller\Index;

class Post extends \Magento\Framework\App\Action\Action{
    protected $collectionModel;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Addify\PasswordProtected\Model\ResourceModel\Passwords\CollectionFactory $collectionModel,
        \Addify\PasswordProtected\Model\PasswordProtectedFactory $ppModel,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Addify\PasswordProtected\Model\PasswordAnalytics $passwordAnalytics
    ) {
        $this->collectionModel = $collectionModel;
        $this->coreSession = $coreSession;
        $this->passwordAnalytics = $passwordAnalytics;
        $this->ppModel = $ppModel;
        $this->messageManager = $messageManager;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;

        parent::__construct($context);
    }
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->coreSession->start();
        $redirectData =$this->coreSession->getPasswordProductRedirect();
        $data = $this->getRequest()->getPostValue();

        if($data && $redirectData):

             $password = $data['pps_password'];
           $collection = $this->collectionModel->create()->addFieldToFilter('password',$password);
            $collection->getSelect()->join(
                ['pp'=>$collection->getTable('addify_passwordprotected')],
                'main_table.pp_id = pp.pp_id',
                ['product_ids'=>'pp.product_ids','cms_ids'=>'pp.cms_ids','category_ids'=>'pp.category_ids']);

            if($redirectData['type']=='category'):
                $collection->addFieldToFilter('pp.category_ids', array('finset' => $redirectData['category_id']));
            elseif($redirectData['type']=='product'):
                $collection->addFieldToFilter('pp.product_ids', array('finset' => $redirectData['product_id']));
            elseif($redirectData['type']=='cms'):
                $collection->addFieldToFilter('pp.cms_ids', array('finset' => $redirectData['cms_id']));
            endif;

            if($collection->getData()):
                $collection = $collection->getFirstItem();
                $ppData = $this->coreSession->getPasswordProductUpdatedData();
                if($ppData):
                    $products =$ppData['product'];
                    $categories =$ppData['category'];
                    $cms   = $ppData['cms'];
                else:
                $products =[];
                $categories =[];
                $cms   = [];
                endif;
                $products =explode(',',$collection->getProductIds());
                foreach ($products as $pro):
                    $products[]=$pro;
                endforeach;
                $category =explode(',',$collection->getCategoryIds());
                foreach ($category as $cate):
                    $categories[]=$cate;

                endforeach;
                $cmsIds =explode(',',$collection->getCmsIds());
                foreach ($cmsIds as $cmsId):
                    $cms[]=$cmsId;

                endforeach;


                $passwordProductData = array('product'=>$products,'category'=>$categories,'cms'=>$cms);

                $this->coreSession->setPasswordProductUpdatedData($passwordProductData);

                $types = array('collections');
                foreach ($types as $type) {
                    $this->cacheTypeList->cleanType($type);
                }
                foreach ($this->cacheFrontendPool as $cacheFrontend) {
                    $cacheFrontend->getBackend()->clean();
                }

                $ip = $this->getRealIpAddr();
//                $ip = '103.255.6.102';

                $details = json_decode($this->get_data("http://ipinfo.io/{$ip}/json"));
                $agent = $_SERVER['HTTP_USER_AGENT'];
                $anaData = array(
                    'password_id'=>$collection->getPasswordId(),
                    'ip_address'=>$ip,
                    'agent'=>$agent,
                    'city'=>$details->city,
                    'region'=>$details->region,
                    'country'=>$details->country,
                    'postcode'=>$details->postal,
                    'org'=>$details->org,
                );
                $this->passwordAnalytics->setData($anaData)->save();
                $this->coreSession->unsPasswordProductRedirect();
               $resultRedirect->setUrl($redirectData['redirect_url']);
               return $resultRedirect;

            endif;

            $this->messageManager->addError(__("Password is incorrect"));
            return $resultRedirect->setPath('passwordprotected');


        else:

           return $resultRedirect->setPath('customer/account');

        endif;



    }
    public function getRealIpAddr()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
        {
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
        {
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else
        {
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        $ips = explode(',',$ip);
        return $ips[0];
    }
    public function get_data($url)
    {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}
