<?php
declare(strict_types=1);

namespace Bss\BrandRepresentative\Controller\Traffic;

use Bss\BrandRepresentative\Api\MostViewedRepositoryInterface;
use Bss\BrandRepresentative\Exception\InvalidFormKeyException;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class NewVisit
 */
class NewVisit implements HttpPostActionInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var MostViewedRepositoryInterface
     */
    private $mostViewedRepository;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * NewVisit constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param RequestInterface $request
     * @param MostViewedRepositoryInterface $mostViewedRepository
     * @param JsonFactory $jsonFactory
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        RequestInterface $request,
        MostViewedRepositoryInterface $mostViewedRepository,
        JsonFactory $jsonFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->mostViewedRepository = $mostViewedRepository;
        $this->jsonFactory = $jsonFactory;
        $this->formKeyValidator = $formKeyValidator;
    }

    /**
     * Before execute the action
     *
     * Validate the form_key
     *
     * @throws InvalidFormKeyException
     */
    public function beforeExecute()
    {
        if (!$this->formKeyValidator->validate($this->request)) {
            throw new InvalidFormKeyException(__("Invalid form key!"), null);
        }
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $error = false;
        $message = "OK";
        try {
            $this->beforeExecute();
            $categoryId = $this->request->getParam('category_id');

            if ($categoryId) {
                $this->mostViewedRepository->addVisitNumber($categoryId);
            }
        } catch (InvalidFormKeyException $e) {
            $message = $e->getMessage();
            $error = true;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $message = "Unhandled exception";
            $error = true;
        }
        $result = $this->jsonFactory->create();

        return $result->setData([
            'error' => $error,
            'message' => $message
        ]);
    }
}
