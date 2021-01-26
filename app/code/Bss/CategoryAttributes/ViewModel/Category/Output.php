<?php
namespace Bss\CategoryAttributes\ViewModel\Category;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class Output implements ArgumentInterface
{
	private $catalogOutputHelper;

    public function __construct(
    	\Magento\Catalog\Helper\Output $catalogOutputHelper
    ) {
    	$this->catalogOutputHelper = $catalogOutputHelper;
    }

    public function getCatalogOutputHelper()
    {
        return $this->catalogOutputHelper;
    }
}