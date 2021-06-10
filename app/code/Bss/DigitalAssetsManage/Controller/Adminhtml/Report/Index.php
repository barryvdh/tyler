<?php
declare(strict_types=1);
namespace Bss\DigitalAssetsManage\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * Digital assets report Report page
 */
class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Bss_DigitalAssetsManage::digital_assets_report';

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultPage = $this->pageFactory->create();
        $resultPage->setActiveMenu("Bss_DigitalAssetsManage::storage_report");
        $resultPage
            ->getConfig()
            ->getTitle()
            ->prepend(__("Digital Assets Brand Storage Report"));

        return $resultPage;
    }
}
