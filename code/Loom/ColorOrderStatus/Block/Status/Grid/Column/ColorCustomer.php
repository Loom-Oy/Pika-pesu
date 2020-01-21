<?php

/*
 * @package    Loom_ColorOrderStatus
 * @copyright  Loom Oy - 2020
 */

// @codingStandardsIgnoreFile

namespace Loom\ColorOrderStatus\Block\Status\Grid\Column;

class ColorCustomer extends \Magento\Backend\Block\Widget\Grid\Column
{
    protected $rowId = 0;
    protected $helper;
    protected $collectionFactory;
    
    public function __construct(\Magento\Backend\Block\Template\Context $context,
        \Loom\ColorOrderStatus\Helper\Data $helper,
        \Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory $collectionFactory,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
    }
    
    public function getFrameCallback()
    {
        return [$this, 'decorateAction'];
    }

    public function decorateAction($value, $row, $column, $isExport)
    {
        $item = $this->collectionFactory->create()
            ->addFieldToSelect('status')
            ->addFieldToFilter('entity_id',$row->getEntityId())
            ->getFirstItem();

        $color = $this->helper->getStatusColor($item->getStatus());

        $cell = '<span data-init-row="'.$row->getId().'"/><script>
            require(["jquery"],
                function($) {
                 $(".col-'.$column->getId().' [data-init-row='.$row->getId().']")
                 .closest("tr")
                 .find("td") 
                 .css("background","'.$color.'");
                }
            );
            </script>';

        return $cell;
    }
}
