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

namespace MageVision\DisplayFiltersPerCategory\Block\Adminhtml\Form\Renderer;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Layer\Category\FilterableAttributeList;
use Magento\Catalog\Block\Adminhtml\Form\Renderer\Fieldset\Element;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\Block\Widget\Button;

class Filters extends Element
{
    /**
     * Initialize block template
     */
    protected $_template = 'MageVision_DisplayFiltersPerCategory::category/form/renderer/filters.phtml';

    /**
     * @var FilterableAttributeList
     */
    protected $filterableAttributes;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param Context $context
     * @param FilterableAttributeList $filterableAttributes
     * @param array $data
     */
    public function __construct(
        Context $context,
        FilterableAttributeList $filterableAttributes,
        array $data = []
    ) {
        $this->filterableAttributes = $filterableAttributes;
        parent::__construct($context, $data);
    }

    /**
     * Prepare global layout
     * Add "Add Filter" button to layout
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            Button::class
        )->setData(
            ['label' => __('Add Filter'), 'onclick' => 'return filterControl.addItem()', 'class' => 'add']
        );
        $button->setName('add_filter_item_button');

        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }

    /**
     * Retrieve 'add filter item' button HTML
     *
     * @return string
     */
    public function getAddButtonHtml(): string
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * Retrieve filters
     *
     * @return array
     */
    public function getFilters(): array
    {
        if ($this->options === null) {
            $this->options['cat'] = __('Category');
            foreach ($this->filterableAttributes->getList() as $attribute) {
                $this->options[$attribute['attribute_code']] = __($attribute['frontend_label']);
            }
        }

        return $this->options;
    }

    /**
     * Prepare filter values
     *
     * @return array
     */
    public function getValues(): array
    {
        return $this->getElement()->getValue();
    }
}
