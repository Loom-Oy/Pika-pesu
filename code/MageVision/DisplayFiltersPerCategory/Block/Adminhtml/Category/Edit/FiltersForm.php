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

use Magento\Catalog\Block\Adminhtml\Form;
use Magento\Catalog\Model\Category;
use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use MageVision\DisplayFiltersPerCategory\Block\Adminhtml\Form\Renderer\Filters;

class FiltersForm extends Form
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     *
     * @throws LocalizedException
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->registry = $registry;
        $this->setForm($formFactory->create());
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Form
     *
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $this->getForm()->addField(
            'filters_per_category',
            'text',
            [
                'name' => 'filters_per_category',
                'required' => false,
                'data-form-part' => 'category_form',
                'value' => $this->getCategory()->getData('filters_per_category')
            ]
        );

        $this->getForm()->getElement('filters_per_category')->setRenderer(
            $this->getLayout()->createBlock(Filters::class)
        );

        return parent::_prepareForm();
    }

    /**
     * Retrieve current category instance
     *
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->registry->registry('category');
    }
}
