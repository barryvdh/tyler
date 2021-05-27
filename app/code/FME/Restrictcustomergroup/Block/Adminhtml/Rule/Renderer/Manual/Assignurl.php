<?php
/**
 * Class for Restrictcustomergroup Assignurl
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Block\Adminhtml\Rule\Renderer\Manual;

class Assignurl extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{

    protected $_template = 'FME_Restrictcustomergroup::renderer/manual/assignurl.phtml';
    protected $_coreRegistry;
    protected $_objectManager;
    protected $serializer;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context,
    \Magento\Framework\Registry $registry,
    \Magento\Framework\ObjectManagerInterface $objectmanager,
    \Magento\Framework\Serialize\SerializerInterface $serializer,
    array $data = [])
    {
      $this->serializer = $serializer;
      $this->_objectManager = $objectmanager;
      $this->_coreRegistry = $registry;
      parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
      $this->_element = $element;
      $html = $this->toHtml();
      return $html;
    }

    public function getRuleModel()
    {
      return $this->_coreRegistry->registry('restrictcustomergroup_data');
    }

    public function getunserialize($serializeform)
    {
      return $this->serializer->unserialize($serializeform);
    }
}
