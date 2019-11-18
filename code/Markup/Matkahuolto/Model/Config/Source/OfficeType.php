<?php
namespace Markup\Matkahuolto\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class OfficeType implements OptionSourceInterface
{
    /**
     * Returns array to be used in office search API options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'all', 'label' => __('All')],
            ['value' => 'm', 'label' => __('Matkahuolto offices')],
            ['value' => 't', 'label' => __('Lähellä offices')],
        ];
    }
}
