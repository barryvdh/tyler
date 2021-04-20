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
 * @category  BSS
 * @package   Bss_AdminProductsGridwCategory
 * @author   Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license  http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AdminProductsGridwCategory\Ui;

use Magento\Backend\Model\Auth\Session as AuthSession;
use Bss\AdminProductsGridwCategory\Helper\Data;

class MassAction extends \Magento\Ui\Component\MassAction
{
    /**
     * @var AuthSession
     */
    protected $authSession;
    /**
     * @var \Bss\AdminProductsGridwCategory\Helper\Data
     */
    protected $helper;

    /**
     * MassAction constructor.
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param AuthSession $authSession
     * @param array $components
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        AuthSession $authSession,
        $components,
        Data $helper,
        array $data
    ) {
        $this->authSession = $authSession;
        $this->helper = $helper;
        parent::__construct($context, $components, $data);
    }

    /**
     * Action prepare
     */
    public function prepare()
    {
        parent::prepare();
        if (!$this->helper->getGeneralConfig('enable')) {
            $config = $this->getConfiguration();
            foreach ($config['actions'] as $k => $action) {
                if ($action['type'] == 'category') {
                    unset($config['actions'][$k]);
                }
            }
            $config['actions'] = array_values($config['actions']);
            $this->setData('config', $config);
        }
    }
}
