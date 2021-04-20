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
 * @package    Bss_AdminActionLog
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AdminActionLog\Model;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\CustomerRepository;

/**
 * Class Log
 * @package Bss\AdminActionLog\Model
 */
class Log
{
    /**
     * @var
     */
    protected $_action;

    /**
     * @var string
     */
    protected $_actionName = '';

    /**
     * @var array
     */
    protected $log_details = [];

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var IpAdress
     */
    protected $ipAddress;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\User\Model\User
     */
    protected $user;

    /**
     * @var PostDispatch
     */
    protected $postdispatch;

    /**
     * @var ActionGridFactory
     */
    protected $actionlog;

    /**
     * @var ActionDetailFactory
     */
    protected $logdetail;

    /**
     * @var LoginFactory
     */
    protected $loginlog;

    /**
     * @var \Bss\AdminActionLog\Helper\Data
     */
    protected $helper;

    /**
     * @var null
     */
    protected $name = null;

    /**
     * @var bool
     */
    protected $_skipNextAction = false;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    protected $sendmail = false;

    /**
     * Log constructor.
     *
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\User\Model\User $user
     * @param IpAdress $ipAddress
     * @param PostDispatch $postdispatch
     * @param ActionGridFactory $actionlog
     * @param ActionDetailFactory $logdetail
     * @param LoginFactory $loginlog
     * @param \Bss\AdminActionLog\Helper\Data $helper
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\User\Model\User $user,
        \Bss\AdminActionLog\Model\IpAdress $ipAddress,
        \Bss\AdminActionLog\Model\PostDispatch $postdispatch,
        \Bss\AdminActionLog\Model\ActionGridFactory $actionlog,
        \Bss\AdminActionLog\Model\ActionDetailFactory $logdetail,
        \Bss\AdminActionLog\Model\LoginFactory $loginlog,
        \Bss\AdminActionLog\Helper\Data $helper,
        CustomerRepository $customerRepository
    ) {
        $this->authSession    = $authSession;
        $this->messageManager = $messageManager;
        $this->_objectManager = $objectManager;
        $this->logger         = $logger;
        $this->request        = $request;
        $this->user           = $user;
        $this->ipAddress      = $ipAddress;
        $this->postdispatch   = $postdispatch;
        $this->actionlog      = $actionlog;
        $this->logdetail      = $logdetail;
        $this->loginlog       = $loginlog;
        $this->helper         = $helper;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param $fullActionName
     * @param $actionName
     *
     * @return $this
     */
    public function initAction($fullActionName, $actionName)
    {
        $this->_actionName = $actionName;

        if (isset($this->helper->getActionInfo()[$fullActionName])) {
            $this->_action = $this->helper->getActionInfo()[$fullActionName];
            if (!$this->helper->getGroupActionAllow($this->_action['group_name'])) {
                $this->_action = null;
            }
        }

        if ($this->_skipNextAction) {
            return $this;
        }

        $sessionValue = $this->authSession->getSkipLoggingAction();
        if ($fullActionName == $sessionValue) {
            $this->authSession->setSkipLoggingAction(null);
            $this->_skipNextAction = true;

            return $this;
        }

        if (isset($this->_action['skip_on_back'])) {
            $addValue = $this->_action['skip_on_back'];
            $this->authSession->setSkipLoggingAction($addValue);
        }

        return $this;
    }

    /**
     * @param $model
     * @param $action
     *
     * @return $this|bool
     */
    public function modelAction($model, $action)
    {
        if (!$this->_action || $this->_skipNextAction) {
            return false;
        }
        $eventGroupNode = $this->_action;
        if (isset($eventGroupNode['expected_models'])) {
            if (is_array($eventGroupNode['expected_models'])) {
                $usedModels = $eventGroupNode['expected_models'];
            } else {
                $usedModels = [$eventGroupNode['expected_models'] => []];
            }
        } else {
            return false;
        }

        $additionalData = $skipData = [];
        foreach ($usedModels as $className => $params) {
            if (!$model instanceof $className) {
                return false;
            }

            $log_detail = $this->dataAction($className, $model, ucfirst($action), $eventGroupNode);

            if (!isset($eventGroupNode['post_dispatch'])) {
                $this->setInfo($className, $model);
            }

            if (!is_object($log_detail)) {
                return $this;
            }

            $log_detail->cleanupData();
            if ($log_detail->hasDifference()) {
                $this->addActionsDetail($log_detail);
            }
        }

        return $this;
    }

    /**
     * @param $model
     * @return array
     */
    private function getOriginalData($model)
    {
        if ($model instanceof Customer) {
            return $this->customerRepository->getById($model->getId())->__toArray();
        }
        return $model->getOrigData();
    }

    /**
     * @param $className
     * @param $model
     * @param $action
     * @param $eventGroupNode
     *
     * @return \Bss\AdminActionLog\Model\ActionDetail|bool
     */
    private function dataAction($className, $model, $action, $eventGroupNode)
    {
        if ($action == 'View') {
            $log_detail = true;
        } elseif ($action == 'Delete') {
            $log_detail = $this->logdetail->create();
            if ($eventGroupNode['group_name'] == 'adminhtml_system_config') {
                $this->setInfoForSystemConfig($log_detail, $className, $model);
            } else {
                $log_detail->setSourceData($className)
                    ->setOldValue($this->getOriginalData($model))
                    ->setNewValue(null);
            }
        } else {
            $log_detail = $this->logdetail->create();
            if ($eventGroupNode['group_name'] == 'adminhtml_system_config') {
                $this->setInfoForSystemConfig($log_detail, $className, $model);
            } else {
                $oldValue = $this->getOriginalData($model);
                $newValue = $model->getData();

                if ($model instanceof Product) {
                    if (isset($model->getOrigData('quantity_and_stock_status')['qty'])) {
                        $oldValue['qty'] = $model->getOrigData('quantity_and_stock_status')['qty'];
                    }
                    $productData = $this->request->getParam('product');

                    if (isset($productData['quantity_and_stock_status']['qty'])) {
                        $newValue['qty'] = $productData['quantity_and_stock_status']['qty'];
                        $this->sendmail = true;
                    } else {
                        $newValue['qty'] = '';
                    }
                }

                $log_detail->setSourceData($className)
                    ->setOldValue($oldValue)
                    ->setNewValue($newValue);
            }
        }

        return $log_detail;
    }

    /**
     * @param $className
     * @param $model
     */
    private function setInfo($className, $model)
    {
        if (!$this->name) {
            $this->name = $model->getName();
        }

        if (!$this->name) {
            $this->name = $model->getTitle();
        }

        if (!$this->name &&
            (
                $className == 'Magento\Sales\Model\Order'
                || $className == 'Magento\Sales\Model\Order\Invoice'
                || $className == 'Magento\Sales\Model\Order\Shipment'
                || $className == 'Magento\Sales\Model\Order\Creditmemo'
            )
        ) {
            $this->name = '#' . $model->getIncrementId();
        }

        if (!$this->name && $model->getId()) {
            $this->name = 'Id: ' . $model->getId();
        }
    }

    /**
     * @return $this|bool
     */
    public function logAction()
    {
        if ($this->_skipNextAction
            || $this->_actionName == 'denied'
            || !$this->_action
            || !$this->helper->isEnabled()
            || !$this->helper->getGroupActionAllow($this->_action['group_name'])) {
            return false;
        }

        $logAction = $this->_initLogAction();
        try {
            if (!$this->_callback($logAction)) {
                return false;
            }

            if (!empty($logAction)) {
                $logAction->save();
                $this->_saveActionLogDetails($logAction);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);

            return false;
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function _initLogAction()
    {
        $username = null;
        $userId   = null;
        if ($this->authSession->isLoggedIn()) {
            $userId   = $this->authSession->getUser()->getId();
            $username = $this->authSession->getUser()->getUsername();
        }

        $errors     = $this->messageManager->getMessages()->getErrors();
        $actiontype = $this->helper->getActionType();
        $storeId    = $section = $this->request->getParam('store');
        if (!$storeId) {
            $storeId = 0;
        }
        if ($this->_action) {
            $logAction = $this->actionlog->create()->setData(
                [
                    'group_action' => $this->_action['label'],
                    'info'         => $this->name,
                    'action_type'  => $actiontype[$this->_action['controller_action']],
                    'action_name'  => $this->_action['controller_action'],
                    'ip_address'   => $this->ipAddress->getIpAdress(),
                    'user_id'      => $userId,
                    'user_name'    => $username,
                    'result'       => empty($errors),
                    'store_id'     => $storeId
                ]
            );
        }

        return $logAction;
    }

    /**
     * @param $username
     * @param $status
     * @param null $userId
     *
     * @return $this|void
     */
    public function logAdminLogin($username, $status, $userId = null)
    {
        $this->loginlog->create()->logAdminLogin($username, $status, null, null);

        $eventCode = 'admin_login';
        if (!$this->helper->getGroupActionAllow($eventCode)) {
            return;
        }
        $actiontype = $this->helper->getActionType();
        $storeId    = $section = $this->request->getParam('store');
        if (!$storeId) {
            $storeId = 0;
        }
        $success = (bool) $userId;
        if (!$userId) {
            $userId = $this->user->loadByUsername($username)->getId();
        }

        $fullaction = $this->request->getRouteName() . '_' . $this->request->getControllerName() . '_' . $this->request->getActionName();

        $actionlog = $this->actionlog->create()->setData(
            [
                'group_action' => $eventCode,
                'info'         => $username,
                'action_type'  => 'login',
                'action_name'  => $fullaction,
                'ip_address'   => $this->ipAddress->getIpAdress(),
                'user_id'      => $userId,
                'user_name'    => $username,
                'result'       => $success,
                'store_id'     => $storeId
            ]
        );

        return $actionlog->save();
    }

    /**
     * @param $logAction
     *
     * @return $this|bool
     */
    private function _callback($logAction)
    {
        $callback = 'Generic';

        if (isset($this->_action['post_dispatch'])) {
            $callback = $this->_action['post_dispatch'];
        }

        if (!$this->postdispatch->{$callback}($this->_action, $logAction, $this)) {
            return false;
        }

        return $this;
    }

    /**
     * @param $logAction
     *
     * @return $this|bool
     */
    private function _saveActionLogDetails($logAction)
    {
        if (!$logAction->getId()) {
            return false;
        }

        foreach ($this->log_details as $log_detail) {
            if ($log_detail && ($log_detail->getOldValue() || $log_detail->getNewValue())) {
                $log_detail->setLogId($logAction->getId());
                $this->_saveActionLogDetail($log_detail);

                if ($this->sendmail) {
                    $this->helper->sendEmail($log_detail, $logAction);
                }
            }
        }

        return $this;
    }

    /**
     * @param $log_detail
     *
     * @return bool
     */
    public function _saveActionLogDetail($log_detail)
    {
        try {
            $log_detail->save();
        } catch (\Exception $e) {
            $this->logger->critical($e);

            return false;
        }
    }

    /**
     * @param $log_detail
     *
     * @return $this
     */
    public function addActionsDetail($log_detail)
    {
        $this->log_details[] = $log_detail;

        return $this;
    }

    /**
     * @param $log_detail
     * @param $className
     * @param $model
     */
    public function setInfoForSystemConfig($log_detail, $className, $model)
    {
        $log_detail->setSourceData($className)
            ->setOldValue([$model->getPath() . '_scope_' . $model->getScope() . '_' . $model->getScopeId() => $model->getOldValue()])
            ->setNewValue([$model->getPath() . '_scope_' . $model->getScope() . '_' . $model->getScopeId() => $this->doRestoreToDefaultValue($model)]);
    }

    /**
     * @param $model
     * @return false|int|string
     */
    public function doRestoreToDefaultValue($model)
    {
        $fieldId = $model->getField();
        $modelValue = $model->getData();
        $configPaths = explode('/', $model->getFieldConfig()['path']);
        unset($configPaths[0]);
        foreach ($configPaths as $configPath) {
            $modelValue = $modelValue['groups'][$configPath];
        }
        $modelValue = $modelValue['fields'][$fieldId];
        if (isset($modelValue['inherit']) &&
            $modelValue['inherit'] == 1 &&
            $this->helper->getDefaultValue($model->getPath())) {
            return $this->helper->getDefaultValue($model->getPath());
        }
        return $model->getValue();
    }
}
