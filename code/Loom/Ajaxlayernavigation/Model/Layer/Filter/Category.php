<?php

namespace Loom\Ajaxlayernavigation\Model\Layer\Filter;

class Category extends \MGS\Ajaxlayernavigation\Model\Layer\Filter\Category
{
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $categoryId = $request->getParam($this->_requestVar) ?: $request->getParam('id');
        if (empty($categoryId)) {
            return $this;
        }
       
        $categoryIds = explode(',', $categoryId);
        $categoryIds = array_unique($categoryIds); 
        $productCollection = $this->getLayer()->getProductCollection();

        if ($request->getParam('id') != $categoryId) {  
             $this->appliedFilter = $categoryId;
            if (!$this->filterPlus) {
                $this->filterPlus = true;
            }
            foreach($categoryIds as $catId){
                $productCollection->addCategoriesFilter(['in' => $catId]); 
            }
            $category = $this->getLayer()->getCurrentCategory();
            $child = $category->getCollection()
                ->addFieldToFilter($category->getIdFieldName(), ['in' => $categoryIds])
                ->addAttributeToSelect('name');
            $categoriesInState = [];
            foreach ($categoryIds as $categoryId) {
                if ($currentCategory = $child->getItemById($categoryId)) {
                    $categoriesInState[$currentCategory->getId()] = $currentCategory->getName();
                }
            }
            foreach ($categoriesInState as $key => $category) {
                $state = $this->_createItem($category, $key);
                $this->getLayer()->getState()->addFilter($state);
            }
        }
        return $this;
    }
}