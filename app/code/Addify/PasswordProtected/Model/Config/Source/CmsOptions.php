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
namespace Addify\PasswordProtected\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class CmsOptions implements \Magento\Framework\Option\ArrayInterface
{
    protected $ppFactory;
    public function __construct(
        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $ppFactory
    )
    {
        $this->ppFactory = $ppFactory;
    }
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $ppCollection = $this->ppFactory->create();

        $options = array();
        $options[] = ['label' => 'Select Page',  'value' => '0' ];
        foreach ($ppCollection as $key => $value)
        {
            $options[] = ['label' => $value->getTitle(),  'value' => $value->getPageId() ];
        }
        return $options;
    }

    public  function getOptionArray()
    {
        $ppCollection = $this->tabFactory->create();
        $options = array();
        $options[] = ['label' => 'Select Page',  'value' => '0' ];
        foreach ($ppCollection as $key => $value)
        {
            $options[] = ['label' => $value->getTitle(),  'value' => $value->getPageId() ];
        }
        return $options;
    }

}
