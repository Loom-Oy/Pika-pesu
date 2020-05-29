<?php
/*
 * @package    Loom_Catalog
 * @copyright  Loom Oy - 2020
 */

namespace Loom\Catalog\Model\Product\Attribute\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Manufacturer implements OptionSourceInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectmanager){
        $this->objectManager = $objectmanager;
    }

    /**
     * Filter options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $attributesArrays = array();

        $attribute = $this
            ->objectManager
            ->get(\Magento\Catalog\Api\ProductAttributeRepositoryInterface::class)
            ->get('manufacturer');

        $i = 0;

        foreach ($attribute->getOptions() as $option)
        {
            $attributesArrays[$i] = array(
                'label' => $option->getLabel(),
                'value' => $option->getValue()
            );
            $i++;
        }

        return $attributesArrays;
    }
}
