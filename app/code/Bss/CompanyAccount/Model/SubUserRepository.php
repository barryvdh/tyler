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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Model;

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Api\Data\SubUserSearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Bss\CompanyAccount\Model\ResourceModel\SubUser as UserResource;
use Bss\CompanyAccount\Model\ResourceModel\SubUser\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class SubUserRepository
 *
 * @package Bss\CompanyAccount\Model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubUserRepository implements SubUserRepositoryInterface
{
    /**
     * @var UserResource
     */
    private $userResource;

    /**
     * @var SubUserFactory
     */
    private $userFactory;

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessor
     */
    private $collectionProcessor;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * SubUserRepository constructor.
     *
     * @param UserResource $userResource
     * @param SubUserFactory $userFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessor $collectionProcessor
     */
    public function __construct(
        UserResource $userResource,
        SubUserFactory $userFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        SearchResultsInterfaceFactory $searchResultsFactory,
        \Psr\Log\LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        CollectionProcessor $collectionProcessor
    ) {
        $this->userResource = $userResource;
        $this->userFactory = $userFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
    }

    /**
     * Save sub-user
     *
     * @param SubUserInterface $user
     *
     * @return SubUserInterface|mixed
     * @throws AlreadyExistsException
     * @throws \Exception
     */
    public function save(SubUserInterface $user)
    {
        try {
            return $this->userResource->save($user);
        } catch (AlreadyExistsException $e) {
            throw new AlreadyExistsException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save sub-user: %1',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Get sub-user by id
     *
     * @param int $id
     * @return SubUserInterface
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        try {
            $user = $this->userFactory->create();
            $this->userResource->load($user, $id);

            return $user;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            throw new NoSuchEntityException(__('Can not get this sub-user.'));
        }
    }

    /**
     * Retrieve sub users matching the specified criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @return SubUserSearchResultsInterface
     * @throws \Exception
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResult = $this->searchResultsFactory->create();
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * Delete sub-user
     *
     * @param SubUserInterface $user
     * @return bool|UserResource
     * @throws CouldNotDeleteException
     */
    public function delete(SubUserInterface $user)
    {
        try {
            return $this->userResource->delete($user);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __('%1', $e->getMessage())
            );
        }
    }

    /**
     * Delete sub-user by id
     *
     * @param int $id
     * @return bool|UserResource
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $id)
    {
        try {
            $user = $this->userFactory->create();
            $this->userResource->load($user, $id);

            return $this->delete($user);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete sub-user: %1',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Get quote by sub-user
     *
     * @param int|SubUserInterface $subUser
     * @return null|\Magento\Quote\Api\Data\CartInterface
     */
    public function getQuoteBySubUser($subUser)
    {
        try {
            if (is_int($subUser)) {
                $subUser = $this->getById($subUser);
            }
            $quote = $this->quoteRepository->get($subUser->getQuoteId());
            return $quote->getIsActive() ? $quote : null;
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get list sub-user by role id
     *
     * @param int $roleId
     * @return bool|\Bss\CompanyAccount\Model\ResourceModel\SubUser\Collection
     */
    public function getByRole($roleId)
    {
        try {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('role_id', $roleId);
            return $collection;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validate unique email
     *
     * @param int $customerId
     * @param string $email
     * @param int|null $subId
     *
     * @return void
     * @throws AlreadyExistsException
     */
    public function validateUniqueSubMail($customerId, $email, $subId = null)
    {
        try {
            $this->userResource->validateUniqueSubEmail($customerId, $email, $subId);
        } catch (AlreadyExistsException $e) {
            throw new AlreadyExistsException(__($e->getMessage()));
        }
    }
}
