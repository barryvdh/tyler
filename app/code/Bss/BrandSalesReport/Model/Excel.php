<?php
declare(strict_types=1);

namespace Bss\BrandSalesReport\Model;

use Magento\Framework\Filesystem\File\WriteInterface;

/**
 * Class Excel
 * Custom excel exporter
 */
class Excel extends \Magento\Framework\Convert\Excel
{
    /**
     * Get a Single XML Row
     *
     * Processing for row with children item
     *
     * @param array $row
     * @param boolean $useCallback
     * @return string|array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _getXmlRow($row, $useCallback)
    {
        if ($useCallback && $this->_rowCallback) {
            // phpcs:disable Magento2.Functions.DiscouragedFunction
            $data = call_user_func($this->_rowCallback, $row);

            if (isset($data['rows'])) {
                $rowData['rows'] = [];
                foreach ($data['rows'] as $rowItem) {
                    $rowData['rows'][] = parent::_getXmlRow($rowItem, false);
                }

                return $rowData;
            }
        }

        return parent::_getXmlRow($row, $useCallback);
    }

    /**
     * Write Converted XML Data to Temporary File
     *
     * Custom for row with children
     *
     * @param WriteInterface $stream
     * @param string $sheetName
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function write(WriteInterface $stream, $sheetName = '')
    {
        $stream->write($this->_getXmlHeader($sheetName));

        foreach ($this->_iterator as $dataRow) {
            $rows = $this->_getXmlRow($dataRow, true);
            if (is_array($rows)) {
                foreach ($rows['rows'] as $row) {
                    $stream->write($row);
                }
            } else {
                $stream->write($rows);
            }
        }
        $stream->write($this->_getXmlFooter());
    }
}
