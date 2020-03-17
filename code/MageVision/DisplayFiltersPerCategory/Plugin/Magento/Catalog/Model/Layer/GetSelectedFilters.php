<?php
/**
 * MageVision Display Filters Per Category Extension
 *
 * @category     MageVision
 * @package      MageVision_DisplayFiltersPerCategory
 * @author       MageVision Team
 * @copyright    Copyright (c) 2019 MageVision (http://www.magevision.com)
 * @license      LICENSE_MV.txt or http://www.magevision.com/license-agreement/
 */
declare(strict_types=1);

namespace MageVision\DisplayFiltersPerCategory\Plugin\Magento\Catalog\Model\Layer;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\FilterList;
use MageVision\DisplayFiltersPerCategory\Model\Config;
use Magento\Framework\Exception\NoSuchEntityException;

class GetSelectedFilters
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Add only filters selected filters for current category or remove them
     *
     * @param FilterList $subject
     * @param array $filters
     * @param Layer $layer
     *
     * @return array
     *
     * @throws NoSuchEntityException
     */
    public function afterGetFilters(
        FilterList $subject,
        array $filters,
        Layer $layer
    ): array {

        if ($this->config->isEnabled()) {
            if ($layer->getCurrentCategory()->getData('remove_filters_per_category')) {
                return [];
            }
            $selectedFilters = $layer->getCurrentCategory()->getData('filters_per_category');
            if (!empty($selectedFilters)) {
                $selectedFiltersArray = $this->formatSelectedFilters($selectedFilters);
                $filtersArray = [];
                foreach ($filters as $key => $filter) {
                    if ($filter->hasData('attribute_model')) {
                        $attributeCode = $filter->getAttributeModel()->getAttributeCode();

                        if (array_key_exists($attributeCode, $selectedFiltersArray)) {
                            $filtersArray[$selectedFiltersArray[$attributeCode]] = $filter;
                        }
                    }

                    if ($filter->getRequestVar() == 'cat'
                        && $layer->getCurrentCategory()->getId() != $layer->getCurrentStore()->getRootCategoryId()
                    ) {
                        if (array_key_exists('cat', $selectedFiltersArray)) {
                            $filtersArray[$selectedFiltersArray['cat']] = $filter;
                        }
                    }
                }
                ksort($filtersArray);

                return $filtersArray;
            }
        }
        return $filters;
    }

    /**
     * @param array $selectedFilters
     * @return array
     */
    protected function formatSelectedFilters($selectedFilters): array
    {
        $selectedFiltersArray = [];

        foreach ($selectedFilters as $kes => $selectedFilter) {
            $selectedFiltersArray[$selectedFilter['filter_code']] = $selectedFilter['position'];
        }

        return $selectedFiltersArray;
    }
}
