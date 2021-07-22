<?php
declare(strict_types=1);
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
 * @package    Bss_CustomCommand
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomCommand\Console;

use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory as SearchCriteriaBuilder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Set all downloadable product manage stock is no
 */
class ApplyNoManageStock extends BaseCommand
{
    public const INPUT_ARG = "product_id";

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    protected $stockItemRepository;

    /**
     * ApplyNoManageStock constructor.
     *
     * @param \Magento\Framework\App\State $state
     * @param \Psr\Log\LoggerInterface $logger
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
     * @param string|null $name
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Psr\Log\LoggerInterface $logger,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        string $name = null
    ) {
        $this->state = $state;
        parent::__construct($logger, $name);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->stockItemRepository = $stockItemRepository;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('bss:product_stock:apply_no_manage_stock_for_downloadable');
        $this->setDescription('Update no manage stock for all downloadable product.');
        parent::configure();
    }

    /**
     * Set all dơnloadable product manage stock is no
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            $progressBar = $this->getProgress($output);
            $productIds = $input->getArgument(self::INPUT_ARG);

            /** @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Item\StockItemCriteria $searchCriteria */
            $searchCriteria = $this->searchCriteriaBuilder->create();

            // Add filter type id = downloadable
            $searchCriteria->addFilter("type_id", "type_id", "downloadable");

            if (!empty($productIds)) {
                $searchCriteria->setProductsFilter($productIds);
            }
            $searchResult = $this->stockItemRepository->getList($searchCriteria);
            if ($searchResult->getTotalCount() === 0) {
                $output->writeln("<info>No record found!</info>");
                return false;
            }
            $startProcess = microtime(true);
            $count = 0;

            $progressBar->start($searchResult->getTotalCount());
            foreach ($searchResult->getItems() as $item) {
                $progressBar->setMessage(
                    $item->getProductSku(),
                    "entity_id"
                );

                if ($item->getManageStock() || $item->getUseConfigManageStock()) {
                    try {
                        $item->setManageStock(false);
                        $item->setUseConfigManageStock(false);
                        $this->stockItemRepository->save($item);
                        $count++;
                    } catch (\Exception $e) {
                        $this->logger->critical($e);
                        $output->writeln("");
                        $output->writeln(
                            sprintf(
                                "<error>ERROR: %s on stock item id: %s</error>",
                                $e->getMessage(),
                                $item->getItemId()
                            )
                        );
                    }
                }

                $progressBar->advance();
            }
            $progressBar->finish();
            $output->writeln("");
            $output->writeln(
                sprintf(
                    "<info>Processing complete %s record(s) in %s seconds.</info>",
                    $count,
                    round(microtime(true) - $startProcess, 2)
                )
            );
            return true;
        } catch (\Exception $e) {
            $this->logger->error($e);
            $output->writeln("");
            $output->writeln("<error>Failed to update product stock data!</error>");
            return false;
        }
    }
}
