<?php
namespace Markup\Matkahuolto\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class OfficeSearchApi implements OptionSourceInterface
{
    /**
     * Returns array to be used in office search API options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'v1', 'label' => __('Version 1')],
            ['value' => 'v2', 'label' => __('Version 2')]
        ];
    }
}
