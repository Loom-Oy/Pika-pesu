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

namespace MageVision\DisplayFiltersPerCategory\Model\Category\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\JsonEncoded;
use Magento\Framework\DataObject;

class Filters extends JsonEncoded
{
    /**
     * Filters before saving
     *
     * @param DataObject $object
     *
     * @return $this
     * @throws \Exception
     */
    public function beforeSave($object)
    {
        $attributeName = $this->getAttribute()->getName();
        $filtersData = $object->getData($attributeName);
        $filtersDataNew = [];
        $optionsValues = [];
        if (is_array($filtersData)) {
            foreach ($filtersData as $key => $data) {
                if ($data['delete']) {
                    unset($filtersData[$key]);
                } else {
                    $optionsValues[] = $data['filter_code'];
                    $filtersDataNew[] = $data;
                }
            }
        }

        $duplicates = $this->isUniqueAdminValues($optionsValues);

        if (!empty($duplicates)) {
            throw new \Exception('The values of filters must be unique.');
        }

        $object->setData($attributeName, $filtersDataNew);

        return parent::beforeSave($object);
    }

    /**
     * Throws Exception if not unique values into options.
     *
     * @param array $optionsValues
     *
     * @return bool
     */
    protected function isUniqueAdminValues(array $optionsValues)
    {
        $uniqueValues = array_unique($optionsValues);
        return array_diff_assoc($optionsValues, $uniqueValues);
    }
}
