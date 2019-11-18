<?php
namespace Markup\Matkahuolto\Model\Total\Quote;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Quote\Model\Quote\Address;

class BussiennakkoFee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    protected $quoteValidator = null;
    protected $scopeConfig;

    public function __construct(
      \Magento\Quote\Model\QuoteValidator $quoteValidator,
      ScopeConfigInterface $scopeConfig
    ) {
        $this->quoteValidator = $quoteValidator;
        $this->scopeConfig = $scopeConfig;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        if ($quote->isVirtual() || $shippingAssignment->getShipping()->getAddress()->getAddressType() != Address::TYPE_SHIPPING) {
          return $this;
        }

        $fee = $this->getFee($quote);

        if ($this->_canApplyTotal($quote)) {
          $total->setBaseTotalAmount('bussiennakko_fee', $fee);
          $total->setTotalAmount('bussiennakko_fee', $fee);

          $total->setBaseBussiennakkoFee($fee);
          $total->setBussiennakkoFee($fee);
        }

        // Must be always set for quote
        $quote->setBaseBussiennakkoFee($fee);
        $quote->setBussiennakkoFee($fee);

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array|null
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        if ($this->_canApplyTotal($quote)) {
            return [
                'code' => 'bussiennakko_fee',
                'title' => $this->getLabel(),
                'value' => $this->getFee($quote)
            ];
        }

        return NULL;
    }

    /**
     * Return true if Bussiennakko fee should be applied
     * @param Quote $quote
     * @return bool
     */
    protected function _canApplyTotal(\Magento\Quote\Model\Quote $quote) {
      return ($quote->getPayment()->getMethod() == 'bussiennakko' && $this->_isMatkahuoltoShippingMethod($quote));
    }

    /**
     * Return true if Matkahuolto shipping method is enabled
     */
    protected function _isMatkahuoltoShippingMethod(\Magento\Quote\Model\Quote $quote) {
      $matkahuoltoMethods = ['matkahuolto_80', 'matkahuolto_10', 'matkahuolto_30', 'matkahuolto_34', 'matkahuolto_local', 'matkahuolto_vak'];
      $shippingMethod = $quote->getShippingAddress()->getShippingMethod();

      return in_array($shippingMethod, $matkahuoltoMethods);
    }

    /**
     * Get fee
     */
    public function getFee($quote) {
      return (double) $this->scopeConfig->getValue('payment/bussiennakko/cod_fee', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel() {
        return __('Bussiennakko');
    }
}
