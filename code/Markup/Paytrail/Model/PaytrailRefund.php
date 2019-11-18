<?php

namespace Markup\Paytrail\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Item;

/**
 * Provides functions for refunding Paytrail payments
 */
class PaytrailRefund
{
  protected $_httpClientFactory;
  protected $_storeManager;
  protected $_merchantId;
  protected $_merchantSecret;
  protected $_itemArgs;

  /**
   * Constructor
   */
  public function __construct(
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
  ) {
    $this->_storeManager = $storeManager;
    $this->_httpClientFactory = $httpClientFactory;
  }

  /**
   * Set merchant ID
   */
  public function setMerchantId($merchantId)
  {
    $this->_merchantId = $merchantId;
  }

  /**
   * Set merchant secret
   */
  public function setMerchantSecret($merchantSecret)
  {
    $this->_merchantSecret = $merchantSecret;
  }

  /**
   * Set item arguments
   */
  public function setItemArgs($itemArgs)
  {
    $this->_itemArgs = $itemArgs;
  }

  /**
   * Refund a transaction
   *
   * @param \Magento\Payment\Model\InfoInterface $payment
   * @param float $amount
   * @return $this
   * @throws \Magento\Framework\Exception\LocalizedException
   */
  public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
  {
    // Check amount
    if ($amount <= 0) {
      throw new LocalizedException(__('Invalid amount for refund.'));
    }

    // Check transaction ID
    if (!$payment->getTransactionId()) {
      throw new LocalizedException(__('Invalid transaction ID.'));
    }

    // Check tax rates
    // We don't have access to refunded items and arbitrary amount may be refunded
    // using "Adjustment fee" so we don't know which VAT/TAX rates to refund. Therefore
    // we need to force manual refunding of orders with more than one tax rate.
    if (count($this->_getTaxRates()) !== 1) {
      throw new LocalizedException(__('Cannot refund order with multiple tax rates. Please refund offline.'));
    }

    $body = $this->_buildRefundRequest($payment, $amount);

    if ($this->_postRefundRequest($payment, $body)) {
      return TRUE;
    }

    throw new LocalizedException(__('Error refunding payment. Please try again or refund offline.'));
  }

  /**
   * Build refund request
   *
   * @param \Magento\Payment\Model\InfoInterface $payment
   * @param float $amount
   * @return array
   */
  protected function _buildRefundRequest(\Magento\Payment\Model\InfoInterface $payment, $amount)
  {
    $order = $payment->getOrder();
    $billingAddress = $order->getBillingAddress();

    $taxRates = $this->_getTaxRates();

    $body = [
			'email' => $billingAddress->getEmail(),
			'notifyUrl' => $this->_storeManager->getStore()->getBaseUrl(), // Just use frontpage, refund notifications are not implemented
			'rows' => [
				[
					'amount' => intval(round(floatval($amount) * 100)),
					'description' => '',
					'vatPercent' => reset($taxRates)
				]
			]
		];

    return json_encode($body);
  }

  /**
   * Post refund request
   */
  protected function _postRefundRequest($payment, $body)
  {
    $path = '/merchant/v1/payments/' . $payment->getOrder()->getIncrementId() . '/refunds';
    $url = 'https://api.paytrail.com' . $path;

    $timestamp = strftime('%FT%T%z');

    $headers = $this->_getAuthHeaders($path, $timestamp, $body);
    $headers[\Zend_Http_Client::CONTENT_TYPE] = 'application/json';

    $client = $this->_httpClientFactory->create();
    $client->setUri($url);
    $client->setConfig(['maxredirects' => 5, 'timeout' => 30]);
    $client->setMethod(\Zend_Http_Client::POST);
    $client->setHeaders($headers);
    $client->setRawData($body, 'application/json');

    try {
      $response = $client->request();
    } catch (\Exception $e) {
      throw new LocalizedException(__('Merchant API connection error. Error: %1', [$e->getMessage()]));
    }

    if ($response->getStatus() == '202') {
      return TRUE;
    }

    $body = json_decode($response->getBody());

    if (isset($body->error) && isset($body->error->description)) {
      throw new LocalizedException(__('Merchant API refund error. Error: %1', [$body->error->description]));
    }

    throw new LocalizedException(__('Merchant API refund error. Error: Unknown error'));
  }

  /**
   * Get authentication headers
   */
  protected function _getAuthHeaders($url, $timestamp, $body)
  {
    $signature = $this->_getAuthSignature($url, $timestamp, $body);

    return [
      'Timestamp' => $timestamp,
      'Content-MD5' => $this->_getAuthMd5Body($body),
      'Authorization' => "PaytrailMerchantAPI {$this->_merchantId}:{$signature}"
    ];
  }

  /**
   * Get authentication signature
   */
  protected function _getAuthSignature($url, $timestamp, $body)
  {
    $data = implode("\n", [
      'POST',
      $url,
      "PaytrailMerchantAPI {$this->_merchantId}",
      $timestamp,
      $this->_getAuthMd5Body($body)
    ]);

    return base64_encode(hash_hmac('sha256', $data, $this->_merchantSecret, true));
  }

  /**
   * Get MD5 hash of request body
   */
  protected function _getAuthMd5Body($body)
  {
    return base64_encode(hash('md5', $body, true));
  }

  /**
   * Get tax rates for order
   */
  protected function _getTaxRates()
  {
    $rates = [];

    foreach ($this->_itemArgs as $item) {
      // Ignore zero prices as bundle products don't have price and tax rate set
      // with dynamic pricing.
      if ($item['price'] > 0) {
        $rates[] = round($item['vat'] * 100); // VAT percent is presented in fractions of a hundred
      }
    }

    return array_unique($rates, SORT_NUMERIC);
  }
}
