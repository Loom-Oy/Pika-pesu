<?php

namespace Markup\Matkahuolto\Block\Sales;

use Magento\Framework\View\Element\Template;
use Magento\Framework\DataObject;

class BussiennakkoFee extends Template
{
    protected $_source;
    protected $_order;

    public function getSource()
    {
        return $this->_source;
    }

    public function displayFullSummary()
    {
        return true;
    }

    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_source = $parent->getSource();
        $this->_order = $parent->getOrder();

        $bussiennakkoFee = $this->getSource()->getBussiennakkoFee();

        if ($bussiennakkoFee && $bussiennakkoFee > 0) {
          $fee = new DataObject(
              [
                  'code' => 'bussiennakko_fee',
                  'strong' => false,
                  'value' => $bussiennakkoFee,
                  'label' => __('Bussiennakko'),
              ]
          );

          $parent->addTotal($fee, 'bussiennakko_fee');
        }

        return $this;
    }
}
