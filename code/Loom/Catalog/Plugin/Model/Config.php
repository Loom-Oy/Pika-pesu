<?php
namespace Loom\Catalog\Plugin\Model;

class Config
{
    /**
     * Adding custom options and changing labels
     *
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param [] $options
     * @return []
     */
    public function afterGetAttributeUsedForSortByArray(\Magento\Catalog\Model\Config $catalogConfig, $options)
    {
        //Remove default sorting options
        unset($options['position']);
        unset($options['name']);
        unset($options['price']);
        unset($options['size']);

        //New sorting options
        $options['created'] = __('Created');
        $options['price_new'] = __('Price');
        $options['name_new'] = __('Name');


        return $options;
    }
}