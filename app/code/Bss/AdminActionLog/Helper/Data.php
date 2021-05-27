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

namespace Bss\AdminActionLog\Helper;

use Bss\AdminActionLog\Model\ActionDetail;
use Bss\AdminActionLog\Model\ActionGrid;
use Bss\AdminActionLog\Model\Config\Source\ActionInfo;
use Bss\AdminActionLog\Model\Config\Source\ActionType;
use Bss\AdminActionLog\Model\Log;
use Bss\AdminActionLog\Model\Login;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Email\Model\Template\SenderResolver;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\User\Model\UserFactory;
use Magento\Framework\App\Config\Initial;

/**
 * Class Data
 * @package Bss\AdminActionLog\Helper
 */
class Data extends AbstractHelper
{

    /**
     * @var ActionType
     */
    protected $actiontype;

    /**
     * @var ActionInfo
     */
    protected $actioninfo;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var SenderResolver
     */
    protected $senderResolver;

    /**
     * @var Timezone
     */
    protected $dateTime;

    /**
     * @var UserFactory
     */
    protected $_userFactory;

    /**
     * @var Initial
     */
    protected $initConfig;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ActionType $actionType
     * @param ActionInfo $actionInfo
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param StateInterface $state
     * @param SenderResolver $senderResolver
     * @param Timezone $dateTime
     * @param UserFactory $userFactory
     * @param Initial $initConfig
     */
    public function __construct(
        Context $context,
        ActionType $actionType,
        ActionInfo $actionInfo,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $state,
        SenderResolver $senderResolver,
        Timezone $dateTime,
        UserFactory $userFactory,
        Initial $initConfig
    ) {
        $this->actiontype        = $actionType;
        $this->actioninfo        = $actionInfo;
        $this->transportBuilder  = $transportBuilder;
        $this->storeManager      = $storeManager;
        $this->inlineTranslation = $state;
        $this->senderResolver    = $senderResolver;
        $this->dateTime          = $dateTime;
        $this->_userFactory      = $userFactory;
        $this->initConfig        = $initConfig;

        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag('action_log_bss/general/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return mixed
     */
    public function getTimeClearLog()
    {
        return $this->scopeConfig->getValue('action_log_bss/general/clear_log',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param null $groupaction
     *
     * @return bool
     */
    public function getGroupActionAllow($groupaction = null)
    {
        $group_allow = $this->scopeConfig->getValue('action_log_bss/general/groupaction',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);

        return in_array($groupaction, explode(',', $group_allow));
    }

    /**
     * @return int
     */
    public function getAdminSessionLifetime()
    {
        return (int) $this->scopeConfig->getValue('admin/security/session_lifetime',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isAdminAccountSharingEnabled()
    {
        return $this->scopeConfig->isSetFlag('admin/security/admin_account_sharing',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    public function getActionInfo()
    {
        return $this->actioninfo->toArray();
    }

    /**
     * @return array
     */
    public function getActionType()
    {
        return $this->actiontype->toArray();
    }

    /**
     * Get Email Sender
     * @return int
     */
    public function getEmailSender()
    {
        return $this->scopeConfig->getValue(
            'action_log_bss/email_notification/sender',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Email Receiver
     * @return int
     */
    public function getEmailReceiver()
    {
        return $this->scopeConfig->getValue(
            'action_log_bss/email_notification/receiver',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Qty Change Template
     * @return int
     */
    public function getQtyChangeEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            'action_log_bss/email_notification/qty_change_notification',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param ActionDetail $logDetail
     * @param ActionGrid $logAction
     *
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendEmail($logDetail, $logAction)
    {
        $storeId    = $this->storeManager->getStore()->getId();
        $templateId = $this->getQtyChangeEmailTemplate();
        $sender     = $this->senderResolver->resolve($this->getEmailSender(), $storeId);
        $toEmails   = explode(',', $this->getEmailReceiver());


        try {
            $oldValue = json_decode($logDetail->getOldValue());
            $newValue = json_decode($logDetail->getNewValue());
            $user     = $this->_userFactory->create()->load($logAction->getUserId());

            $templateVars = [
                'oldValue'    => $oldValue->qty,
                'newValue'    => $newValue->qty,
                'className'   => $logAction->getGroupAction(),
                'action'      => $logAction->getActionType(),
                'productName' => $logAction->getInfo(),
                'userName'    => $logAction->getUserName(),
                'fullName'    => $user->getFirstname() . ' ' . $user->getLastname(),
                'email'       => $user->getEmail(),
                'date'        => $this->dateTime->formatDateTime($logAction->getCreatedAt(), \IntlDateFormatter::MEDIUM)
            ];

            $from            = [
                'email' => $sender['email'],
                'name'  => $sender['name']
            ];
            $templateOptions = [
                'area'  => Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $this->inlineTranslation->suspend();

            if (!empty($toEmails)) {
                $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars($templateVars)
                    ->setFrom($from)
                    ->addTo($toEmails[0])
                    ->addCc($toEmails)
                    ->getTransport();
                $transport->sendMessage();
            }

            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
    }

    /**
     * @return int|string
     */
    public function getDefaultValue($path)
    {
        $defaultStoreValue = $this->initConfig->getData('default');
        if (!$path) {
            return false;
        }
        $pathArr = explode('/', $path);
        if (!isset($pathArr[0]) || !isset($pathArr[1]) || !isset($pathArr[2])) {
            return false;
        }
        if (!isset($defaultStoreValue[$pathArr[0]][$pathArr[1]][$pathArr[2]])) {
            return false;
        }
        return $defaultStoreValue[$pathArr[0]][$pathArr[1]][$pathArr[2]];
    }
}
