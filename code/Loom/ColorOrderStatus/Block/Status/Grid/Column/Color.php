<?php

/*
 * @package    Loom_ColorOrderStatus
 * @copyright  Loom Oy - 2020
 */

// @codingStandardsIgnoreFile

namespace Loom\ColorOrderStatus\Block\Status\Grid\Column;

class Color extends \Magento\Backend\Block\Widget\Grid\Column
{
    public function getFrameCallback()
    {
        return [$this, 'decorateAction'];
    }
    
    public function decorateAction($value, $row, $column, $isExport)
    {
        $cell = '';
        $state = $row->getState();
        if (!empty($state)) {

            if($row->getColor()) {
                $cell = '<span style="width:40px;height:15px;margin-left:5px;display: inline-block;background: '.$row->getColor().'">&nbsp;</span>';
            }
        }
        return $cell;
    }
}
