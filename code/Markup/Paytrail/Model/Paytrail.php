<?php

namespace Markup\Paytrail\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Item;

/**
 * Payment method model
 */
class Paytrail extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_CODE = 'paytrail';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_CODE;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapture = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapturePartial = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $_httpClientFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory
     */
    protected $_orderStatusCollectionFactory;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxHelper;

    /**
     * @var \Markup\Paytrail\Model\Config\Source\PaymentMethods
     */
    protected $_paymentMethodsConfig;

    /**
     * @var \Markup\Paytrail\Model\PaytrailRefund
     */
    protected $_paytrailRefund;

    /**
     * Constructor
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $orderStatusCollectionFactory
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $orderStatusCollectionFactory,
        \Magento\Checkout\Model\Session $session,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Tax\Helper\Data $taxHelper,
        \Markup\Paytrail\Model\Config\Source\PaymentMethods $paymentMethodsConfig,
        \Markup\Paytrail\Model\PaytrailRefund $paytrailRefund,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
      parent::__construct(
          $context,
          $registry,
          $extensionFactory,
          $customAttributeFactory,
          $paymentData,
          $scopeConfig,
          $logger,
          $resource,
          $resourceCollection,
          $data
      );

      $this->_orderStatusCollectionFactory = $orderStatusCollectionFactory;
      $this->_session = $session;
      $this->_httpClientFactory = $httpClientFactory;
      $this->_urlBuilder = $urlBuilder;
      $this->_request = $request;
      $this->_taxHelper = $taxHelper;
      $this->_paymentMethodsConfig = $paymentMethodsConfig;
      $this->_paytrailRefund = $paytrailRefund;
    }

    public function initialize($paymentAction, $stateObject)
    {
      /** @var \Magento\Quote\Model\Quote\Payment $info */
      $info = $this->getInfoInstance();

      /** @var \Magento\Sales\Model\Order $order */
      $order = $info->getOrder();

      // Get Paytrail payment page URL
      // Customer will be redirected to this address after hitting "Place Order"
      // button. The URL will be provided via AJAX request to the checkout page
      // and then redirected with JS.
      $url = FALSE;
      if (!$this->usePaymentPageBypass()) {
        $url = $this->_requestPaymentUrl($order);
      }

      // Prevent sending order confirmation email before payment has been done
      $order->setCanSendNewEmailFlag(false);

      if ($url != FALSE || $this->usePaymentPageBypass()) {
        $order->addStatusHistoryComment(__('The customer was redirected to Paytrail.'), \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $order->save();

        $status = $this->getState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $stateObject->setState($status->getState());
        $stateObject->setStatus($status->getStatus());
        $stateObject->setIsNotified(false);

        if (!$this->usePaymentPageBypass()) {
          $this->_session->setPaytrailRedirectUrl($url);
        }
      } else {
        throw new LocalizedException(__('Cannot get payment URL from Paytrail'));
      }

      return $this;
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
      $this->_paytrailRefund->setItemArgs($this->_itemArgs($payment->getOrder()));
      $this->_paytrailRefund->setMerchantId($this->_getMerchantId());
      $this->_paytrailRefund->setMerchantSecret($this->_getMerchantSecret());

      // Exceptions are handled in the refund class
      if ($this->_paytrailRefund->refund($payment, $amount)) {
        return $this;
      }
    }

    public function getState($status)
    {
      $collection = $this->_orderStatusCollectionFactory->create()->joinStates();
      $status = $collection->addAttributeToFilter('main_table.status', $status)->getFirstItem();
      return $status;
    }

    private function _requestPaymentUrl($order)
    {
      $auth = base64_encode( $this->_getMerchantId() . ':' . $this->_getMerchantSecret() );

      $requestBody = $this->_requestBody($order);

      // Validate that all order items were included and totals match
      if (!$this->_validateTotals($order, $requestBody, FALSE)) {
        throw new LocalizedException(__("Unexpected error (totals do not match)."));
      }

      $client = $this->_httpClientFactory->create();
      $client->setUri('https://payment.paytrail.com/api-payment/create');
      $client->setConfig(['maxredirects' => 5, 'timeout' => 30]);
      $client->setMethod(\Zend_Http_Client::POST);
      $client->setHeaders('Authorization', 'Basic ' . $auth);
      $client->setHeaders('X-Verkkomaksut-Api-Version', '1');
      $client->setHeaders(\Zend_Http_Client::CONTENT_TYPE, 'application/json');
      $client->setRawData(json_encode($requestBody), 'application/json');

      $body = FALSE;
      try {
        $body = $client->request()->getBody();
      } catch (\Exception $e) {
        throw new LocalizedException(__('Cannot get payment URL from Paytrail'));
      }

      if ($body) {
        $bodyJson = json_decode($body);

        if (isset($bodyJson->url) && !empty($bodyJson->url)) {
          return $bodyJson->url;
        }
      }

      return FALSE;
    }

    /**
     * Get payment page bypass setting
     */
    public function usePaymentPageBypass()
    {
      return (bool) $this->getConfigData('payment_page_bypass');
    }

    /**
     * Get enabled payment page bypass groups
     */
    public function getEnabledPaymentMethodGroups()
    {
      $groups = [
        ['id' => 'bank', 'title' => __('Online Banking')],
        ['id' => 'cc', 'title' => __('Credit / Debit Card')],
        ['id' => 'invoice_part', 'title' => __('Invoice / Part Payment')],
        ['id' => 'mobile_pay', 'title' => __('MobilePay')],
        ['id' => 'paypal', 'title' => __('PayPal')],
      ];

      // Add methods to groups
      foreach ($groups as $key => $group) {
        $groups[$key]['methods'] = $this->_getEnabledPaymentMethodsByGroup($group['id']);

        // Remove empty groups
        if (empty($groups[$key]['methods'])) {
          unset($groups[$key]);
        }
      }

      return array_values($groups);
    }

    /**
     * Get enabled payment methods by group
     */
    protected function _getEnabledPaymentMethodsByGroup($groupId)
    {
      $enabledMethodIds = explode(',', $this->getConfigData('payment_page_bypass_methods'));
      $allMethods = $this->_paymentMethodsConfig->toOptionArray();
      $methods = [];

      foreach ($allMethods as $key => $method) {
        if (in_array($method['value'], $enabledMethodIds) && $method['group'] == $groupId) {
          $methods[] = [
            'id' => $method['value'],
            'title' => $method['label'],
            'group' => $method['group'],
          ];
        }
      }

      return $methods;
    }

    /**
     * Get merchant ID
     */
    private function _getMerchantId()
    {
      return $this->getConfigData('merchant_id');
    }

    /**
     * Get merchant secret
     */
    private function _getMerchantSecret()
    {
      return $this->getConfigData('merchant_secret');
    }

    /**
     * Get locale
     */
    private function _getLocale()
    {
      $locale = $this->getConfigData('locale');

      return empty($locale) ? 'fi_FI' : $locale;
    }

    /**
     * Get instructions
     *
     * @return string
     */
    public function getInstructions()
    {
      return trim($this->getConfigData('instructions'));
    }

    /**
     * Get payment page redirect URL
     */
    public function getPaymentRedirectUrl()
    {
      return 'paytrail/redirect';
    }

    /**
     * Get form action URL (E2 version)
     */
    public function getFormAction()
    {
      return 'https://payment.paytrail.com/e2';
    }

    /**
     * Get form fields (Paytrail E2 version)
     */
    public function getFormFields($order, $paymentMethodId = NULL)
    {
      $billingAddress = $order->getBillingAddress();

      $receiptUrl = $this->_urlBuilder->getUrl('paytrail/receipt', [
        '_secure' => $this->_request->isSecure(),
        '_query' => ['e2' => '1']
      ]);

      $formFields = [
        'MERCHANT_ID' => $this->_getMerchantId(),
        'CURRENCY' => $order->getOrderCurrencyCode(),
        'URL_SUCCESS' => $receiptUrl,
        'URL_CANCEL' => $receiptUrl,
        'ORDER_NUMBER' => $order->getIncrementId(),
        'PARAMS_IN' => '',
        'PARAMS_OUT' => 'ORDER_NUMBER,PAYMENT_ID,AMOUNT,CURRENCY,PAYMENT_METHOD,TIMESTAMP,STATUS,SETTLEMENT_REFERENCE_NUMBER',
        'ALG' => '1',
        'URL_NOTIFY' => $receiptUrl,
        'LOCALE' => $this->_getLocale(),
        'VAT_IS_INCLUDED' => '1',
        'PAYER_PERSON_FIRSTNAME' => $this->_preprocessValue('default', $billingAddress->getFirstname(), 64),
        'PAYER_PERSON_LASTNAME' => $this->_preprocessValue('default', $billingAddress->getLastname(), 64),
        'PAYER_PERSON_EMAIL' => $billingAddress->getEmail(),
        'PAYER_PERSON_PHONE' => $this->_preprocessValue('phone', $billingAddress->getTelephone()),
        'PAYER_PERSON_ADDR_STREET' => $this->_preprocessValue('address', $billingAddress->getStreetLine(1), 128),
        'PAYER_PERSON_ADDR_POSTAL_CODE' => $this->_preprocessValue('postcode', $billingAddress->getPostcode()),
        'PAYER_PERSON_ADDR_TOWN' => $this->_preprocessValue('default', $billingAddress->getCity(), 64),
        'PAYER_PERSON_ADDR_COUNTRY' => $billingAddress->getCountryId(),
        'PAYER_COMPANY_NAME' => $this->_preprocessValue('default', $billingAddress->getCompany(), 128),
      ];

      foreach ($this->_itemArgs($order) as $i => $item) {
        $formFields["ITEM_TITLE[{$i}]"] = $this->_preprocessValue('default', $item['title'], 255);
        $formFields["ITEM_QUANTITY[{$i}]"] = $item['amount'];
        $formFields["ITEM_UNIT_PRICE[{$i}]"] = $item['price'];
        $formFields["ITEM_VAT_PERCENT[{$i}]"] = $item['vat'];
        $formFields["ITEM_DISCOUNT_PERCENT[{$i}]"] = $item['discount'];
        $formFields["ITEM_TYPE[{$i}]"] = $item['type'];
      }

      // Set preselected payment method ID
      if (!empty($paymentMethodId)) {
        $formFields['PAYMENT_METHODS'] = $paymentMethodId;
      }

      // Set all form fields to be included in authcode calculation
      $formFields['PARAMS_IN'] = implode(',', array_keys($formFields));

      // Calculate authcode
      $formFields['AUTHCODE'] = $this->_calculateAuthcode($formFields);

      // Validate that all order items were included and totals match
      if (!$this->_validateTotals($order, $formFields, TRUE)) {
        throw new LocalizedException(__("Unexpected error (totals do not match)."));
      }

      return $formFields;
    }

    /**
     * Removes forbidden characters from the form field value
     *
     * See http://docs.paytrail.com/en/index-all.html#idm140264538415424
     */
    private function _preprocessValue($processor, $value, $maxLength = FALSE)
    {
      if ($processor == 'default') {
        $regex = '/[^\pL-0-9- "\',()\[\]{}*\/+\-_,.:&!?@#$£=*;~]/u';
        $value = preg_replace($regex, '', $value);
      } else if ($processor == 'address') {
        $regex = '/[^\pL 0-9]/u';
        $value = preg_replace($regex, '', $value);
      } else if ($processor == 'phone') {
        $regex = '/[^0-9\+\-]/u';
        $value = preg_replace($regex, '', $value);
      } else if ($processor == 'postcode') {
        $regex = '/[^A-Za-z0-9]/u';
        $value = preg_replace($regex, '', $value);
      }

      if ($maxLength !== FALSE) {
        $value = substr($value, 0, $maxLength);
      }

      return $value;
    }

    private function _calculateAuthcode($fields)
    {
      $values = array_merge([$this->_getMerchantSecret()], array_values($fields));

      return strtoupper(hash("sha256", implode("|", $values)));
    }

    private function _requestBody($order)
    {
      $billingAddress = $order->getBillingAddress();

      $receiptUrl = $this->_urlBuilder->getUrl('paytrail/receipt', [
        '_secure' => $this->_request->isSecure()
      ]);

      $data = array(
  			'orderNumber' => $order->getIncrementId(),
  			'currency' => $order->getOrderCurrencyCode(),
  			'locale' => $this->_getLocale(),
  			'urlSet' => array(
  				'success' => $receiptUrl,
  				'failure' => $receiptUrl,
  				'notification' => $receiptUrl,
  			),
  			'orderDetails' => array(
  				'includeVat' => '1',
  				'contact' => array(
  					'firstName' => $billingAddress->getFirstname(),
  					'lastName' => $billingAddress->getLastname(),
  					'companyName' => $billingAddress->getCompany(),
  					'email' => $billingAddress->getEmail(),
  					'telephone' => $billingAddress->getTelephone(),
  					'mobile' => $billingAddress->getTelephone(),
  					'address' => array(
  						'street' => $billingAddress->getStreetLine(1),
  						'postalCode' => $billingAddress->getPostcode(),
  						'postalOffice' => $billingAddress->getCity(),
  						'country' => $billingAddress->getCountryId(),
  					),
  				),
  				'products' => $this->_itemArgs($order),
  			),
  		);

      return $data;
    }

    private function _itemArgs($order)
    {
      $items = array();

        # Add line items
        /** @var $item Item */
        foreach ($order->getAllItems() as $key => $item) {
            // When in grouped or bundle product price is dynamic (product_calculations = 0)
            // then also the child products has prices so we set
            if($item->getChildrenItems() && !$item->getProductOptions()['product_calculations']) {
                $items[] = array(
                    'title'    => sprintf( '%d x %s', $item->getQtyOrdered(), $item->getName() ),
                    'code'     => '',
                    'amount'   => 1,
                    'price'    => 0,
                    'vat'      => 0,
                    'discount' => 0,
                    'type'     => 1
                );
            } else {
                $items[] = array(
                    'title'    => sprintf( '%d x %s', $item->getQtyOrdered(), $item->getName() ),
                    'code'     => '',
                    'amount'   => 1,
                    'price'    => floatval($item->getRowTotalInclTax()),
                    'vat'      => round(floatval($item->getTaxPercent())),
                    'discount' => 0,
                    'type'     => 1
                );
            }
      }

      # Add shipping
      if ( ! $order->getIsVirtual()) {
        $shippingExclTax = $order->getShippingAmount();
        $shippingInclTax = $order->getShippingInclTax();
        $shippingTaxPct = 0;
        if ($shippingExclTax > 0) {
          $shippingTaxPct = ($shippingInclTax - $shippingExclTax) / $shippingExclTax * 100;
        }

        if ($order->getShippingDescription()) {
          $shippingLabel = $order->getShippingDescription();
        } else {
          $shippingLabel = __('Shipping');
        }

        $items[] = array(
          'title' => $shippingLabel,
          'code' => '',
          'amount' => 1,
          'price' => floatval($order->getShippingInclTax()),
          'vat' => round(floatval($shippingTaxPct)),
          'discount' => 0,
          'type' => 2
        );
      }

      # Add discount
      if (abs($order->getDiscountAmount()) > 0) {
        $discountData = $this->_getDiscountData($order);
        $discountInclTax = $discountData->getDiscountInclTax();
        $discountExclTax = $discountData->getDiscountExclTax();
        $discountTaxPct = 0;
        if ($discountExclTax > 0) {
          $discountTaxPct = ($discountInclTax - $discountExclTax) / $discountExclTax * 100;
        }

        if ($order->getDiscountDescription()) {
          $discountLabel = $order->getDiscountDescription();
        } else {
          $discountLabel = __('Discount');
        }

        $items[] = array(
          'title' => (string) $discountLabel,
          'code' => '',
          'amount' => 1,
          'price' => floatval($discountData->getDiscountInclTax()) * -1,
          'vat' => round(floatval($discountTaxPct)),
          'discount' => 0,
          'type' => 3
        );
      }

      // Round all prices to two decimals
      foreach ($items as $key => $item) {
        $items[$key]['price'] = round($item['price'], 2);
      }

      return $items;
    }

    /**
     * Validate that order total and line item totals match
     */
    protected function _validateTotals($order, $requestBody, $e2 = FALSE)
    {
      if ($e2) {
        return $this->_validateE2Totals($order, $requestBody);
      }

      return $this->_validateRestTotals($order, $requestBody);
    }

    /**
     * Validate totals for REST API requests
     */
    protected function _validateRestTotals($order, $requestBody)
    {
      $requestTotal = 0;
      foreach ($requestBody['orderDetails']['products'] as $key => $product) {
        $requestTotal += $product['price'] * $product['amount'];
      }

      return $this->totalsMatch($order->getGrandTotal(), $requestTotal);
    }

    /**
     * Validate totals for E2 requests
     */
    protected function _validateE2Totals($order, $requestBody)
    {
      $requestTotal = 0;
      foreach ($this->_itemArgs($order) as $i => $item) {
        $requestTotal += $item['price'] * $item['amount'];
      }

      return $this->totalsMatch($order->getGrandTotal(), $requestTotal);
    }

    /**
     * Check if order grand total and items total match
     *
     * Magento uses 4 decimals to calculate grand total so there may be rounding
     * error of 1 cent which is allowed
     */
    protected function totalsMatch($grandTotal, $itemsTotal) {
      $oneCentMore = round($itemsTotal - 0.01, 2) == round($grandTotal, 2);
      $oneCentLess = round($itemsTotal + 0.01, 2) == round($grandTotal, 2);
      $exactly = round($itemsTotal, 2) == round($grandTotal, 2);

      return ($exactly || $oneCentMore || $oneCentLess);
    }

    /**
     * Verify payment when using REST API implementation
     */
    public function verifyPayment($orderNo, $timestamp, $paid, $method, $authCode)
    {
      $merchantSecret = $this->_getMerchantSecret();
      $base = "{$orderNo}|{$timestamp}|{$paid}|{$method}|{$merchantSecret}";

      return ($authCode === strtoupper(md5($base))) && !empty($paid) && ($paid != '0000000000');
    }

    /**
     * Verify payment when using payment page bypass implementation (E2)
     */
    public function verifyPaymentE2($orderNo, $paymentId, $amount, $currency, $paymentMethod, $timestamp, $status, $settlementReference, $authCode)
    {
      $merchantSecret = $this->_getMerchantSecret();
      $base = "{$orderNo}|{$paymentId}|{$amount}|{$currency}|{$paymentMethod}|{$timestamp}|{$status}|{$settlementReference}|{$merchantSecret}";

      return ($authCode === strtoupper(hash("sha256", $base)) && $status === 'PAID');
    }

    /**
     * Add transaction
     * @param \Magento\Sales\Model\Order $order
     * @param array $details
     * @return Transaction
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addPaymentTransaction(\Magento\Sales\Model\Order $order, array $details = [])
    {
      /** @var \Magento\Sales\Model\Order\Payment\Transaction $transaction */
      $transaction = null;

      $transaction = $order->getPayment()->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE, null, true);
      $transaction->isFailsafe(true)->close(false);
      $transaction->setAdditionalInformation(\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS, $details);
      $transaction->save();

      return $transaction;
    }


    /**
     * Gets the total discount for order
     */
    private function _getDiscountData(\Magento\Sales\Model\Order $order)
    {
      $discountIncl = 0;
      $discountExcl = 0;

      // Get product discount amounts
      foreach ($order->getAllItems() as $item) {
        if (!$this->_taxHelper->priceIncludesTax()) {
          $discountExcl += $item->getDiscountAmount();
          $discountIncl += $item->getDiscountAmount() * (($item->getTaxPercent() / 100) + 1);
        } else {
          $discountExcl += $item->getDiscountAmount() / (($item->getTaxPercent() / 100) + 1);
          $discountIncl += $item->getDiscountAmount();
        }
      }

      // Get shipping tax rate
      if ((float) $order->getShippingInclTax() && (float) $order->getShippingAmount()) {
        $shippingTaxRate = $order->getShippingInclTax() / $order->getShippingAmount();
      } else {
        $shippingTaxRate = 1;
      }

      // Add / exclude shipping tax
      $shippingDiscount = (float) $order->getShippingDiscountAmount();
      if (!$this->_taxHelper->priceIncludesTax()) {
        $discountIncl += $shippingDiscount * $shippingTaxRate;
        $discountExcl += $shippingDiscount;
      } else {
        $discountIncl += $shippingDiscount;
        $discountExcl += $shippingDiscount / $shippingTaxRate;
      }

      $return = new \Magento\Framework\DataObject;
      return $return->setDiscountInclTax($discountIncl)->setDiscountExclTax($discountExcl);
    }
}
