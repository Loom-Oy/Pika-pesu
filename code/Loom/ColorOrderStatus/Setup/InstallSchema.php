<?php

/*
 * @package    Loom_ColorOrderStatus
 * @copyright  Loom Oy - 2020
 */

// @codingStandardsIgnoreFile

namespace Loom\ColorOrderStatus\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order_status'),
            'color',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'size' => 7,
                'default' => '',
                'comment' => 'Color'
            ]
        );

        $setup->endSetup();
    }
}
