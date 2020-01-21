<?php

/*
 * @package    Loom_ColorOrderStatus
 * @copyright  Loom Oy - 2020
 */

// @codingStandardsIgnoreFile

namespace Loom\ColorOrderStatus\Ui\Component\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class ColorOrder extends Column
{
    protected $_helper;
    
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['status']) && !empty($item['status'])) {
                    $item['color_order'] = $this->getColor($item['status']);
                }
            }
        }
        return $dataSource;
    }

    protected function getColor($status)
    {
        if(!$this->_helper)
            $this->_helper =  \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Loom\ColorOrderStatus\Helper\Data::class);
        return   $this->_helper->getStatusColor($status);
    }
}
