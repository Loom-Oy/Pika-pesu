<?php

namespace Markup\Matkahuolto\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Sales\Model\Order;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    protected $salesSetupFactory;
    protected $quoteSetupFactory;

    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.3') < 0) {
          $this->upgradeTo203($setup);
        }

        if (version_compare($context->getVersion(), '2.0.4') < 0) {
          $this->upgradeTo204($setup);
        }

        if (version_compare($context->getVersion(), '2.0.11') < 0) {
          $this->upgradeTo2011($setup);
        }

        $setup->endSetup();
    }

    /**
     * Upgrade to version 2.0.3
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    protected function upgradeTo203(ModuleDataSetupInterface $setup) {
        $attributes = [
            'bussiennakko_fee' => 'Bussiennakko Fee',
            'base_bussiennakko_fee' => 'Bussiennakko Base Fee',
        ];

        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        foreach ($attributes as $attributeCode => $attributeLabel) {
            $salesSetup->addAttribute('order', $attributeCode, ['type' => 'decimal']);
            $salesSetup->addAttribute('order_address', $attributeCode, ['type' => 'decimal']);
        }

        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
        foreach ($attributes as $attributeCode => $attributeLabel) {
            $quoteSetup->addAttribute('quote', $attributeCode, ['type' => 'decimal']);
            $quoteSetup->addAttribute('quote_address', $attributeCode, ['type' => 'decimal']);
        }
    }

    /**
     * Upgrade to version 2.0.4
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    protected function upgradeTo204(ModuleDataSetupInterface $setup)
    {
        $attributes = [
            'bussiennakko_fee' => 'Bussiennakko Fee',
            'base_bussiennakko_fee' => 'Bussiennakko Base Fee',
        ];

        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        foreach ($attributes as $attributeCode => $attributeLabel) {
            $salesSetup->addAttribute('invoice', $attributeCode, ['type' => 'decimal']);
            $salesSetup->addAttribute('creditmemo', $attributeCode, ['type' => 'decimal']);
        }
    }

    /**
     * Upgrade to version 2.0.11
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    protected function upgradeTo2011(ModuleDataSetupInterface $setup)
    {
      $installer = $setup;

      $installer->startSetup();

      $installer->getConnection()->addColumn(
        $installer->getTable('quote_address'),
        'matkahuolto_agent_data',
        [
          'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
          'nullable' => true,
          'comment' => 'Matkahuolto Agent Data',
        ]
      );

      $installer->getConnection()->addColumn(
        $installer->getTable('sales_order_address'),
        'matkahuolto_agent_data',
        [
          'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
          'nullable' => true,
          'comment' => 'Matkahuolto Agent Data',
        ]
      );

      $setup->endSetup();
    }
}
