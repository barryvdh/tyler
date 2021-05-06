<?php
/**
 * Class for Restrictcustomergroup InlineEdit
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action\Context;
use FME\Restrictcustomergroup\Model\Rule;
use Magento\Framework\Controller\Result\JsonFactory;

class InlineEdit extends \Magento\Backend\App\Action
{

    /** @var PostDataProcessor */
    protected $dataProcessor;

    /** @var Restrictcustomergroup  */
    protected $_restrictcustomergroupRule;

    /** @var JsonFactory  */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param PostDataProcessor $dataProcessor
     * @param Index $restrictcustomergroupRule
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        PostDataProcessor $dataProcessor,
        Rule $restrictcustomergroupRule,
        JsonFactory $jsonFactory
    ) {

        parent::__construct($context);
        $this->dataProcessor = $dataProcessor;
        $this->_restrictcustomergroupRule = $restrictcustomergroupRule;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData(
                [
                        'messages' => [__('Please correct the data sent.')],
                        'error' => true,
                ]
            );
        }
        foreach (array_keys($postItems) as $id) {
            /** @var \Magento\Restrictcustomergroup\Model\Rule $restrictcustomergroupRule */
            $model = $this->_restrictcustomergroupRule->load($id);
            try {
                $ruleData = $this->filterPost($postItems[$id]);
                $this->validatePost($ruleData, $model, $error, $messages);
                $extendedRuleData = $model->getData();
                $this->setRuleData($model, $extendedRuleData, $ruleData);

                $model->setData($model->getData());
                $model->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithRuleId($model, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithRuleId($model, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithRuleId(
                    $model,
                    __('Something went wrong while saving the Rule.')
                );
                $error = true;
            }
        }
        return $resultJson->setData(
            [
                    'messages' => $messages,
                    'error' => $error
            ]
        );
    }

    /**
     * Add RestrictcustomergroupRule title to error message
     *
     * @param Index $restrictcustomergroupRule
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithRuleId(Rule $restrictcustomergroupRule, $errorText)
    {
        return '[Restrictcustomergroup ID: ' . $restrictcustomergroupRule->getId() . '] ' . $errorText;
    }

    /**
     * Filtering posted data.
     *
     * @param array $postData
     * @return array
     */
    protected function filterPost($postData = [])
    {
        $ruleData = $this->dataProcessor->filter($postData);

        return $ruleData;
    }

    /**
     * Validate post data
     *
     * @param array $ruleData
     * @param \FME\Restrictcustomergroup\Model\Rule $rule
     * @param bool $error
     * @param array $messages
     * @return void
     */
    protected function validatePost(
        array $ruleData,
        \FME\Restrictcustomergroup\Model\Rule $rule,
        &$error,
        array &$messages
    ) {

        if (!($this->dataProcessor->validate($ruleData) && $this->dataProcessor->validateRequireEntry($ruleData))) {
            $error = true;
            foreach ($this->messageManager->getMessages(true)->getItems() as $error) {
                $messages[] = $this->getErrorWithRuleId($rule, $error->getText());
            }
        }
    }

    /**
     * Set restrictcustomergroup rule data
     *
     * @param \FME\Restrictcustomergroup\Model\Rule $rule
     * @param array $extendedRuleData
     * @param array $ruleData
     * @return $this
     */
    public function setRuleData(\FME\Restrictcustomergroup\Model\Rule $rule, array $extendedRuleData, array $ruleData)
    {
        $rule->setData(array_merge($rule->getData(), $extendedRuleData, $ruleData));
        return $this;
    }

    protected function _isAllowed()
    {
        return true;
    }
}
