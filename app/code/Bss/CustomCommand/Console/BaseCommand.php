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

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends \Symfony\Component\Console\Command\Command
{
    const INPUT_ARG = "my_arg";
    const DEFAULT_BAR_CHAR = '<fg=green>⚬</>';
    const DEFAULT_PROGRESS_CHAR = "\xF0\x9F\x8D\xBA";
    const DEFAULT_REDRAW_FREQ = 1;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * BaseCommand constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param string|null $name
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        string $name = null
    ) {
        $this->logger = $logger;
        parent::__construct($name);
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDefinition($this->getInputList());
        parent::configure();
    }

    /**
     * Init progress
     *
     * @param OutputInterface $output
     * @param string|null $format
     * @return ProgressBar
     */
    protected function getProgress(OutputInterface $output, string $format = null): ProgressBar
    {
        $progressBar = new ProgressBar($output);
        $progressBar->setBarCharacter(static::DEFAULT_BAR_CHAR);
        $progressBar->setProgressCharacter(static::DEFAULT_PROGRESS_CHAR);
        $progressBar->setRedrawFrequency(static::DEFAULT_REDRAW_FREQ);
        if ($format === null) {
            $format = sprintf(
                "Processing Entity: <comment>%%entity_id%%</comment>" .
                "%s%%current%%/%%max%% [%%bar%%] %%percent:3s%%%% - Estimated %%estimated:-6s%%",
                // phpcs:disable Magento2.Functions.DiscouragedFunction
                chr(10)
            );
        }
        $progressBar->setFormat($format);

        return $progressBar;
    }

    /**
     * Get list of options and arguments for the command
     *
     * @return InputArgument[]
     */
    public function getInputList(): array
    {
        return [
            new InputArgument(
                static::INPUT_ARG,
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Space-separated list of ' . static::INPUT_ARG
            ),
        ];
    }
}
