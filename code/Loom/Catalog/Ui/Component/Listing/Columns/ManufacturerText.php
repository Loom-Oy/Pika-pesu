<?php
/*
 * @package    Loom_Catalog
 * @copyright  Loom Oy - 2020
 */

namespace Loom\Catalog\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
/**
 * @api
 * @since 100.0.2
 */
class ManufacturerText extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ObjectManagerInterface $objectManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->objectManager = $objectmanager;
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
            $fieldName = $this->getData('name');
            $productBlock = $this->objectManager->get('Magento\Catalog\Model\Product')->getResource()->getAttribute('manufacturer');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName])) {
                    $item[$fieldName] = $productBlock->getSource()->getOptionText($item[$fieldName]);
                }
            }
        }

        return $dataSource;
    }
}
