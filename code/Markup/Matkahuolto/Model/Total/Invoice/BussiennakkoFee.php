<?php
namespace Markup\Matkahuolto\Model\Total\Invoice;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote;

class BussiennakkoFee extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    public function collect(Invoice $invoice) {
        $order = $invoice->getOrder();
        $invoice->setBussiennakkoFee($order->getBussiennakkoFee());
        $invoice->setBaseBussiennakkoFee($order->getBaseBussiennakkoFee());
        if ($this->_canApplyTotal($order)) {
            $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getBussiennakkoFee());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getBaseBussiennakkoFee());
        }

        return $this;
    }

    /**
     * Return true if Bussiennakko fee should be applied
     * @param Order $order
     * @return bool
     */
    protected function _canApplyTotal(Order $order) {
      return ($order->getPayment()->getMethod() == 'bussiennakko' && $this->_isMatkahuoltoShippingMethod($order));
    }

    /**
     * Return true if Matkahuolto shipping method is enabled
     */
    protected function _isMatkahuoltoShippingMethod(Order $order) {
      $matkahuoltoMethods = ['matkahuolto_80', 'matkahuolto_10', 'matkahuolto_30', 'matkahuolto_34', 'matkahuolto_local', 'matkahuolto_vak'];
      $shippingMethod = $order->getShippingMethod();

      return in_array($shippingMethod, $matkahuoltoMethods);
    }
}
