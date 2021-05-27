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
namespace Addify\PasswordProtected\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Addify\PasswordProtected\Block\Adminhtml\PasswordProtected\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;

/**
 * Class PageActions
 */
class PasswordActions extends Column
{
    /** Url path */

    const EXTRAPRODUCTTAB_URL_PATH_DELETE = 'passwordprotected/passwords/delete';
    const EXTRAPRODUCTTAB_URL_PATH_PASS = 'passwordprotected/passwordsAnalytics/index';

    /** @var UrlBuilder */
    protected $actionUrlBuilder;

    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @var string
     */
    private $editUrl;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlBuilder $actionUrlBuilder
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $passUrl = self::EXTRAPRODUCTTAB_URL_PATH_PASS
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
        $this->passUrl = $passUrl;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['password_id'])) {

                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::EXTRAPRODUCTTAB_URL_PATH_DELETE, ['id' => $item['password_id'],'pp_id' => $item['pp_id']]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete ${ $.$data.title }'),
                            'message' => __('Are you sure you wan\'t to delete a ${ $.$data.title } record?')
                        ]
                    ];
                    $item[$name]['passwordsanalytics'] = [
                        'href' => $this->urlBuilder->getUrl($this->passUrl, ['password_id' => $item['password_id'],'pp_id' => $item['pp_id']]),
                        'label' => __('Analytics')
                    ];
 
                }
            }

        return $dataSource;
        }
    }
}