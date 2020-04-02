<?php
/*
 * @package    Loom_Catalog
 * @copyright  Loom Oy - 2020
 */

namespace Loom\Catalog\Plugin\Product\ProductList;

class Toolbar
{
    /**
    * Plugin
    *
    * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
    * @param \Closure $proceed
    * @param \Magento\Framework\Data\Collection $collection
    * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
    */
    public function aroundSetCollection(\Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar,\Closure $proceed,$collection) {

        $toolbar->setDefaultDirection('desc');
        $toolbar->setDefaultOrder('created');
        $this->_collection = $collection;
        $currentOrder = $toolbar->getCurrentOrder();
        $currentDirection = $toolbar->getCurrentDirection();
        $result = $proceed($collection);

        if ($currentOrder) {
            switch ($currentOrder) {
                    case 'created':
                        if ($currentDirection == 'desc') {
                            $this->_collection->setOrder('created_at', 'desc');   
                        } elseif ($currentDirection == 'asc') {
                            $this->_collection->setOrder('created_at', 'asc');            
                        }
                        break;

                    case 'price_new':
                        if ($currentDirection == 'desc') {
                            $this->_collection->setOrder('price', 'desc');
                        } elseif ($currentDirection == 'asc') {
                            $this->_collection->setOrder('price', 'asc');      
                        }
                        break;

                        case 'name_new':
                            if ($currentDirection == 'desc') {
                                $this->_collection->setOrder('name', 'desc');
                            } elseif ($currentDirection == 'asc') {
                                $this->_collection->setOrder('name', 'asc');      
                            }
                            break;

            default:        
                $this->_collection
                    ->setOrder($currentOrder, $currentDirection);
            break;

            }
        }

        return $result;
    }
}
