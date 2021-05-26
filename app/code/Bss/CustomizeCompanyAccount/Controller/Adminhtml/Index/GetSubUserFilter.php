<?php
declare(strict_types=1);

namespace Bss\CustomizeCompanyAccount\Controller\Adminhtml\Index;

use Bss\CustomizeCompanyAccount\Model\InitSubUserFieldCollection;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface as Request;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\View\Result\LayoutFactory as ResultLayoutFactory;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;

/**
 * Class GetSubUserFilter
 * Get sub-user fields filter grid
 */
class GetSubUserFilter implements ActionInterface, HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ResultLayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var ResultRedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var InitSubUserFieldCollection
     */
    protected $initSubUserFieldCollection;

    /**
     * GetSubUserFilter constructor.
     *
     * @param Request $request
     * @param ResultLayoutFactory $resultLayoutFactory
     * @param MessageManager $messageManager
     * @param ResultRedirectFactory $redirectFactory
     * @param InitSubUserFieldCollection $initSubUserFieldCollection
     */
    public function __construct(
        Request $request,
        ResultLayoutFactory $resultLayoutFactory,
        MessageManager $messageManager,
        ResultRedirectFactory $redirectFactory,
        InitSubUserFieldCollection $initSubUserFieldCollection
    ) {
        $this->request = $request;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->messageManager = $messageManager;
        $this->redirectFactory = $redirectFactory;
        $this->initSubUserFieldCollection = $initSubUserFieldCollection;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if ($this->getRequest()->isXmlHttpRequest() && $data) {
            try {
                /** @var \Magento\Framework\View\Result\Layout $resultLayout */
                $resultLayout = $this->resultLayoutFactory->create();
                /** @var $attrFilterBlock \Magento\ImportExport\Block\Adminhtml\Export\Filter */
                $attrFilterBlock = $resultLayout->getLayout()->getBlock('export.sub.user.filter');

                $attrFilterBlock->prepareCollection(
                    $this->initSubUserFieldCollection->execute()
                );
                return $resultLayout;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        } else {
            $this->messageManager->addErrorMessage(__('Please correct the data sent value.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->redirectFactory->create();
        $resultRedirect->setPath('adminhtml/*/index');
        return $resultRedirect;
    }

    /**
     * Get request object
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}
