<?php

/*
 * @package    Loom_ColorOrderStatus
 * @copyright  Loom Oy - 2020
 */

// @codingStandardsIgnoreFile

namespace Loom\ColorOrderStatus\Helper;

use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $collection;
    protected $statusColor;

    public function __construct(
        Context $context,
        \Magento\Sales\Model\ResourceModel\Order\Status\Collection $collection
    ) {
        parent::__construct($context);
        $this->collection = $collection;
    }

    public function getStatusColor($status)
    {
        $statues = $this->getColorStatuses();
        return (isset($statues[$status])) ? $statues[$status] : '';
    }
    
    public function getColorStatuses()
    {
        if (!isset($this->statusColor)) {
            foreach ($this->collection->getItems() as $item) {
                $this->statusColor[$item->getStatus()] = $item->getColor();
            }
        }
        return $this->statusColor;
    }
}
