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

namespace MageVision\DisplayFiltersPerCategory\Block\Adminhtml\Category\Edit;

use Magento\Backend\Block\Template;
use Magento\Framework\Exception\LocalizedException;

class Filters extends Template
{
    /**
     * @return string
     *
     * @throws LocalizedException
     */
    public function toHtml(): string
    {
        return $this->getLayout()->createBlock(
            FiltersForm::class,
            'filters-per-category-form'
        )->toHtml();
    }
}
