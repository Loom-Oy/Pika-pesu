<?php
namespace Markup\Matkahuolto\Model\Total\Creditmemo;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote;

class BussiennakkoFee extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    public function collect(Creditmemo $creditmemo) {
        $order = $creditmemo->getOrder();
        $creditmemo->setBussiennakkoFee($order->getBussiennakkoFee());
        $creditmemo->setBaseBussiennakkoFee($order->getBaseBussiennakkoFee());
        if ($this->_canApplyTotal($order)) {
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getBussiennakkoFee());
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getBaseBussiennakkoFee());
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
