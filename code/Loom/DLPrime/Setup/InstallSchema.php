<?php

namespace Loom\DLPrime\Setup;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 *
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
      $installer = $setup;
      $installer->startSetup();
      $installer->getConnection()->addColumn(
          $installer->getTable('sales_flat_order'),
          'is_order_file_generated',
          [
              'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
              'nullable' => false,
              'comment' => 'Flag for DL Prime orders',
          ]
      );
      $setup->endSetup();
    }
}
