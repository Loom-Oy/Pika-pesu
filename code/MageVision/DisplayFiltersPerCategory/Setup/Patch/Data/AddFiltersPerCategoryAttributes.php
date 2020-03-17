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

namespace MageVision\DisplayFiltersPerCategory\Setup\Patch\Data;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use MageVision\DisplayFiltersPerCategory\Model\Category\Attribute\Backend\Filters;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Setup\CategorySetup;

class AddFiltersPerCategoryAttributes implements DataPatchInterface
{
    /**
     */
    private $moduleDataSetup;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * AddFiltersPerCategoryAttribute constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var CategorySetup $catalogSetup */
        $catalogSetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $catalogSetup->addAttribute(
            Category::ENTITY,
            'filters_per_category',
            [
                'type'     => 'text',
                'label'    => 'Filters Per Category',
                'input'    => 'text',
                'backend'  => Filters::class,
                'source'   => '',
                'visible'  => true,
                'default'  => '',
                'required' => false,
                'sort_order' => 120,
                'input_renderer' => '',
                'global'   => ScopedAttributeInterface::SCOPE_STORE,
                'group'    => 'Display Settings',
            ]
        );

        $catalogSetup->addAttribute(
            Category::ENTITY,
            'remove_filters_per_category',
            [
                'type'     => 'int',
                'label'    => 'Remove Filters Per Category',
                'input'    => 'select',
                'backend'  => '',
                'source' => Boolean::class,
                'visible'  => true,
                'default'  => '0',
                'required' => false,
                'sort_order' => 130,
                'global'   => ScopedAttributeInterface::SCOPE_STORE,
                'group'    => 'Display Settings',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.3.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
