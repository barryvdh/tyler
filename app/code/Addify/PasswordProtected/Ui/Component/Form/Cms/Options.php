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
 Namespace Addify\PasswordProtected\Ui\Component\Form\Cms;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as     PageCollectionFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Options tree for "Categories" field
 */
class Options implements OptionSourceInterface
{

    protected $pageCollectionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var array
     */
    protected $cmsOption;

    /**
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param RequestInterface $request
     */
    public function __construct(
        PageCollectionFactory $pageCollectionFactory,
        RequestInterface $request
    ) {
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getCmsOption();
    }

    /**
     * Retrieve categories tree
     *
     * @return array
     */
    protected function getCmsOption()
    {
        if ($this->getCmsOption() === null) {
            $collection = $this->pageCollectionFactory->create();

            $collection->addAttributeToSelect('name');
            foreach ($collection as $page) {
                $pageId = $page->getEntityId();
                if (!isset($categoryById[$pageId])) {
                    $cmsById[$pageId] = [
                        'value' => $pageId
                    ];
                }
                $cmsById[$pageId]['label'] = $page->getTitle();
            }
            $this->cmsOption = $pageId;
        }
        return $this->cmsOption;
    }
}