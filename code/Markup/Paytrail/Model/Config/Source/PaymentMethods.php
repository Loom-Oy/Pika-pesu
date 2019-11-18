<?php
/**
 * Provides options for payment page bypass methods
 */
namespace Markup\Paytrail\Model\Config\Source;

class PaymentMethods implements \Magento\Framework\Option\ArrayInterface
{
  /**
   * Options getter
   *
   * @return array
   */
  public function toOptionArray()
  {
    return [
      ['value' => '1', 'label' => __('Nordea'), 'group' => 'bank'],
      ['value' => '2', 'label' => __('Osuuspankki'), 'group' => 'bank'],
      ['value' => '3', 'label' => __('Danske Bank'), 'group' => 'bank'],
      ['value' => '5', 'label' => __('Ålandsbanken'), 'group' => 'bank'],
      ['value' => '6', 'label' => __('Handelsbanken'), 'group' => 'bank'],
      ['value' => '10', 'label' => __('S-Pankki'), 'group' => 'bank'],
      ['value' => '50', 'label' => __('Aktia'), 'group' => 'bank'],
      ['value' => '51', 'label' => __('POP Pankki'), 'group' => 'bank'],
      ['value' => '52', 'label' => __('Säästöpankki'), 'group' => 'bank'],
      ['value' => '61', 'label' => __('Oma Säästöpankki'), 'group' => 'bank'],
      ['value' => '11', 'label' => __('Klarna, Invoice'), 'group' => 'invoice_part'],
      ['value' => '12', 'label' => __('Klarna, Installment'), 'group' => 'invoice_part'],
      ['value' => '18', 'label' => __('Jousto'), 'group' => 'invoice_part'],
      ['value' => '60', 'label' => __('Collector Bank'), 'group' => 'invoice_part'],
      ['value' => '30', 'label' => __('Visa'), 'group' => 'cc'],
      ['value' => '31', 'label' => __('MasterCard'), 'group' => 'cc'],
      ['value' => '34', 'label' => __('Diners Club'), 'group' => 'cc'],
      ['value' => '35', 'label' => __('JCB'), 'group' => 'cc'],
      ['value' => '53', 'label' => __('Visa (Nets)'), 'group' => 'cc'],
      ['value' => '54', 'label' => __('MasterCard (Nets)'), 'group' => 'cc'],
      ['value' => '55', 'label' => __('Diners Club (Nets)'), 'group' => 'cc'],
      ['value' => '56', 'label' => __('American Express (Nets)'), 'group' => 'cc'],
      ['value' => '58', 'label' => __('MobilePay'), 'group' => 'mobile_pay'],
      ['value' => '9', 'label' => __('Paypal'), 'group' => 'paypal'],
    ];
  }

  /**
   * Get options in "key-value" format
   *
   * @return array
   */
  public function toArray()
  {
    $options = [];
    foreach ($this->toOptionArray() as $option) {
      $options[$option['value']] = $option['label'];
    }

    return $options;
  }
}
