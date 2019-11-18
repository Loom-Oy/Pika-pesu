<?php
/**
 * Provides options for locale
 */
namespace Markup\Paytrail\Model\Config\Source;

class Locale implements \Magento\Framework\Option\ArrayInterface
{
  /**
   * Options getter
   *
   * @return array
   */
  public function toOptionArray()
  {
    return [
      ['value' => 'fi_FI', 'label' => __('Finnish')],
      ['value' => 'en_US', 'label' => __('English')],
      ['value' => 'sv_SE', 'label' => __('Swedish')],
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
