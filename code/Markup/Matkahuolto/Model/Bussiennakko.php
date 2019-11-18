<?php
namespace Markup\Matkahuolto\Model;

/**
 * Bussiennakko payment method model
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 */
class Bussiennakko extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_CODE = 'bussiennakko';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_CODE;

    /**
     * Payment block paths
     *
     * @var string
     */
    protected $_formBlockType = 'Markup\Matkahuolto\Block\Form\Bussiennakko';

    /**
     * Info instructions block path
     *
     * @var string
     */
    protected $_infoBlockType = 'Magento\Payment\Block\Info\Instructions';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * Check whether method is available
     *
     * @param \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
      return parent::isAvailable($quote) && null !== $quote && $this->isMatkahuoltoShippingMethod($quote);
    }

    /**
     * Check whether Matkahuolto shipping method is selected
     *
     * @param \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote|null $quote
     * @return bool
     */
    public function isMatkahuoltoShippingMethod(\Magento\Quote\Api\Data\CartInterface $quote = null) {
      $matkahuoltoMethods = ['matkahuolto_80', 'matkahuolto_10', 'matkahuolto_30', 'matkahuolto_34', 'matkahuolto_local', 'matkahuolto_vak'];
      $shippingMethod = $quote->getShippingAddress()->getShippingMethod();

      return in_array($shippingMethod, $matkahuoltoMethods);
    }

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
      return trim($this->getConfigData('instructions'));
    }

    /**
     * Get IBAN for cash on delivery
     */
    public function getCodIban() {
      return trim($this->getConfigData('cod_iban'));
    }

    /**
     * Get BIC code for cash on delivery
     */
    public function getCodBic() {
      return trim($this->getConfigData('cod_bic'));
    }

    /**
     * Calculate Finnish banking reference from order ID
     * for cash on delivery.
     */
    public function calculateReference($order) {
      $orderId = (int) $order->getIncrementId();

      // Add "10" to base as 3 characters is minimum for banking reference
      $base = '10' . strval($orderId);
      $base = str_split(strval($base));

      $weights = array( 7, 3, 1, 7, 3, 1, 7, 3, 1, 7, 3, 1, 7, 3, 1, 7, 3, 1, 7 );
  		$reversed_base = array_reverse( $base );

  		$sum = 0;
  		for ( $i = 0; $i < count( $reversed_base ); $i++ ) {
  			$coefficient = array_shift( $weights );
  			$sum += $reversed_base[$i] * $coefficient;
  		}

  		$checksum = ( $sum % 10 == 0 ) ? 0 : ( 10 - $sum % 10 );

  		$reference = implode( '', $base ) . $checksum;

  		return $reference;
    }
}
