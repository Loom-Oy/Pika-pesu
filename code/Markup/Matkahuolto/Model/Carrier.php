<?php
namespace Markup\Matkahuolto\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Config;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Psr\Log\LoggerInterface;
use Magento\Framework\Xml\Security;
use Magento\Shipping\Helper\Carrier as CarrierHelper;

/**
 * Class Carrier Matkahuolto carrier model
 */
class Carrier extends AbstractCarrierOnline implements CarrierInterface
{
    protected $_code = 'matkahuolto';
    protected $_gatewayUrl = 'https://extservices.matkahuolto.fi/mpaketti/mhshipmentxml';
    protected $_gatewayUrlTest = 'https://extservicestest.matkahuolto.fi/mpaketti/mhshipmentxml';

    protected $_isFixed = true;
    protected $_logger;
    protected $_carrierHelper;
    protected $_httpClientFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
      \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
      \Psr\Log\LoggerInterface $logger,
      Security $xmlSecurity,
      \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
      \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
      \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
      \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
      \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
      \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
      \Magento\Directory\Model\RegionFactory $regionFactory,
      \Magento\Directory\Model\CountryFactory $countryFactory,
      \Magento\Directory\Model\CurrencyFactory $currencyFactory,
      \Magento\Directory\Helper\Data $directoryData,
      \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
      CarrierHelper $carrierHelper,
      \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
      array $data = []
    ) {
      $this->_carrierHelper = $carrierHelper;
      $this->_httpClientFactory = $httpClientFactory;
      $this->_logger = $logger;
      parent::__construct(
        $scopeConfig,
        $rateErrorFactory,
        $logger,
        $xmlSecurity,
        $xmlElFactory,
        $rateFactory,
        $rateMethodFactory,
        $trackFactory,
        $trackErrorFactory,
        $trackStatusFactory,
        $regionFactory,
        $countryFactory,
        $currencyFactory,
        $directoryData,
        $stockRegistry,
        $data
      );
    }

    /**
     * Generates list of carrier's all shipping methods
     *
     * @return array
     */
    public function getMethods() {
      return array(
        '80' => $this->getConfigData('title_80'),
        '10' => $this->getConfigData('title_10'),
        '30' => $this->getConfigData('title_30'),
        '34' => $this->getConfigData('title_34'),
        'local' => $this->getConfigData('title_local'),
        'vak' => $this->getConfigData('title_vak'),
      );
    }

    /**
     * Generates list of allowed carrier`s shipping methods
     * Displays on cart price rules page
     *
     * @return array
     * @api
     */
    public function getAllowedMethods()
    {
      $methods = $this->getMethods();
      $allowed_methods = array();

      foreach ($methods as $key => $method_title) {
        if ($this->getConfigData("active_{$key}")) {
          $allowed_methods[$key] = $method_title;
        }
      }

      return $allowed_methods;
    }

    /**
     * Collect and get rates for storefront
     *
     * @param RateRequest $request
     * @return DataObject|bool|null
     * @api
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->isActive()) {
            return false;
        }

        $this->setRawRequest($request);

        $result = $this->_rateFactory->create();

        $allowed_methods = $this->getAllowedMethods();
        foreach ($allowed_methods as $method_key => $method_title) {
          $price = $this->findRate($request, $method_key);

          // Package weight exceeds the highest possible
          if ($price === FALSE) {
            continue;
          }

          $method = $this->_rateMethodFactory->create();
          $method->setCarrier($this->_code);
          $method->setCarrierTitle('Matkahuolto');

          $method->setMethod($method_key);
          $method->setMethodTitle($method_title);
          $method->setCost($this->getMethodPrice($price, $method_key));
          $method->setPrice($this->getMethodPrice($price, $method_key));
          $result->append($method);
        }

        return $result;
    }

    /**
     * Calculate price considering free shipping
     *
     * @param string $cost
     * @param string $method
     * @return float|string
     * @api
     */
    public function getMethodPrice($cost, $method = '') {
      $free_shipping_subtotal = $this->getConfigData('free_shipping_subtotal_' . $method);
      $subtotal = $this->_rawRequest->getBaseSubtotalInclTax();

      if ($free_shipping_subtotal > 0 && $free_shipping_subtotal <= $subtotal) {
        return '0.00';
      }

      return $cost;
    }

    public function findRate(RateRequest $request, $methodKey) {
      $prices = $this->_getPriceData($methodKey);

      $packageWeight = $request->getPackageWeight();

      foreach ($prices as $price) {
        if ($packageWeight <= $price['max_weight']) {
          return $price['price'];
        }
      }

      return FALSE;
    }

    /**
     * Get price data for a shipping method
     */
    protected function _getPriceData($methodKey)
    {
      // Magento 2.1 used PHP serializer while 2.2 uses JSON
      // We need to check which one to use
      $prices = $this->getConfigData("price_{$methodKey}");

      if ($this->_validJson($prices)) {
        return json_decode($prices, TRUE);
      }

      return unserialize($prices);
    }

    /**
     * Validates JSON
     */
    private function _validJson($string)
    {
      json_decode($string);
      return (json_last_error() == JSON_ERROR_NONE);
    }

    public function processAdditionalValidation(\Magento\Framework\DataObject $request)
    {
      return TRUE;
    }

    public function proccessAdditionalValidation(\Magento\Framework\DataObject $request)
    {
      return TRUE;
    }

    /**
     * Do request to shipment
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return array|\Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function requestToShipment($request) {
        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
          throw new \Magento\Framework\Exception\LocalizedException(__('No packages for request'));
        }

        $result = $this->_doShipmentRequest($request);

        $data = [];

        if ( ! $result->hasErrors()) {
          $data[] = [
            'tracking_number' => $result->getTrackingNumber(),
            'label_content' => $result->getShippingLabelContent(),
          ];

          $request->setMasterTrackingId($result->getTrackingNumber());
        }

        $response = new \Magento\Framework\DataObject(['info' => $data]);
        if ($result->hasErrors()) {
          $response->setErrors($result->getErrors());
        }

        return $response;
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request) {
      $this->_prepareShipmentRequest($request);

      // Calculate total package weight
      $packageWeight = 0;
      foreach ($request->getPackages() as $piece) {
        $packageWeight += $this->_convertWeight($piece['params']['weight'], $piece['params']['weight_units']);
      }
      $request->setPackageWeight($packageWeight);

      $result = new \Magento\Framework\DataObject();

      $requestXml = $this->_formShipmentRequest($request);

      $debugData = ['request' => $requestXml];

      if ($this->getConfigData('mode')) {
        $url = $this->_gatewayUrl;
      } else {
        $url = $this->_gatewayUrlTest;
      }

      $client = $this->_httpClientFactory->create();
      $client->setUri($url);
      $client->setConfig(['maxredirects' => 5, 'timeout' => 30]);
      $client->setMethod(\Zend_Http_Client::POST);
      $client->setHeaders(\Zend_Http_Client::CONTENT_TYPE, 'text/xml');
      $client->setRawData($requestXml, 'text/xml');

      $response = $client->request();
      $response = $this->parseXml($response->getBody());

      if ($response === false || isset($response->ErrorNbr)) {
        $debugData['result'] = [
          'error' => $response->ErrorMsg,
          'code' => $response->ErrorNbr,
          'xml' => $response->asXML(),
        ];
        $this->_debug($debugData);
        $result->setErrors($debugData['result']['error']);
      } else {
        $labelContent = base64_decode((string)$response->ShipmentPdf);
        $trackingNumber = (string)$response->Shipment->ShipmentNumber;
        $result->setShippingLabelContent($labelContent);
        $result->setTrackingNumber($trackingNumber);
      }

      $result->setGatewayResponse($response);
      $debugData['result'] = $response;
      $this->_debug($debugData);

      return $result;
    }

    /**
     * Form XML for shipment request
     *
     * @param \Magento\Framework\DataObject $request
     * @return string
     */
    protected function _formShipmentRequest(\Magento\Framework\DataObject $request)
    {
      if ($request->getReferenceData()) {
        $referenceData = $request->getReferenceData();
      } else {
        $referenceData = '#' . $request->getOrderShipment()->getOrder()->getIncrementId();
      }

      $productCode = $request->getShippingMethod();

      // VAK shipments not yet supported
      if ($productCode == 'vak') {
        throw new \Magento\Framework\Exception\LocalizedException(__('VAK shipments not supported.'));
      }

      if ($productCode == 'local') {
        // Package will be left on the same agent as it will be delivered to
        // Service code is 10 = Bussipaketti
        $productCode = '10';
        $agentId = $this->getConfigData('local_agentid');
      } else {
        $agentId = $this->_findAgentId($request);
      }

      $xml = $this->_xmlElFactory->create(['data' => '<MHShipmentRequest></MHShipmentRequest>']);

      $xml->addChild('UserId', $this->getConfigData('userid'));
      $xml->addChild('Password', $this->getConfigData('password'));
      $xml->addChild('Version', '2.0');

      $shipment = $xml->addChild('Shipment');
        $shipment->addChild('ShipmentType', 'N'); # Normal shipment
        $shipment->addChild('MessageType', 'N'); # New shipment
        $shipment->addChild('ShipmentNumber', '');
        $shipment->addChild('ShipmentDate', '');
        $shipment->addChild('Weight', $request->getPackageWeight());
        $shipment->addChild('Packages', count($request->getPackages()));
        $shipment->addChild('SenderId', $this->getConfigData('userid'));
        $shipment->addChild('SenderName1', "Seinäjoen Pika-Pesu Oy");
        $shipment->addChild('SenderName2', '');
        $shipment->addChild('SenderAddress', $request->getShipperAddressStreet());
        $shipment->addChild('SenderPostal', $request->getShipperAddressPostalCode());
        $shipment->addChild('SenderCity', $request->getShipperAddressCity());
        $shipment->addChild('SenderContactName', $request->getShipperContactPersonName());
        $shipment->addChild('SenderContactNumber', $request->getShipperContactPhoneNumber());
        $shipment->addChild('SenderEmail', '');
        $shipment->addChild('SenderReference', $referenceData);
        $shipment->addChild('DeparturePlaceCode', '');
        $shipment->addChild('DeparturePlaceName', '');
        $shipment->addChild('ReceiverId', '');
        $receiverName1 = $request->getRecipientContactCompanyName() ? $request->getRecipientContactCompanyName() : $request->getRecipientContactPersonName();
        $shipment->addChild('ReceiverName1', $receiverName1);
        $shipment->addChild('ReceiverName2', '');
        $shipment->addChild('ReceiverAddress', $request->getRecipientAddressStreet());
        $shipment->addChild('ReceiverPostal', $request->getRecipientAddressPostalCode());
        $shipment->addChild('ReceiverCity', $request->getRecipientAddressCity());
        $receiverContactName = $request->getRecipientContactCompanyName() ? $request->getRecipientContactPersonName() : '';
        $shipment->addChild('ReceiverContactName', $receiverContactName);
        $shipment->addChild('ReceiverContactNumber', $request->getRecipientContactPhoneNumber());
        $shipment->addChild('ReceiverEmail', $request->getOrderShipment()->getOrder()->getCustomerEmail());
        $shipment->addChild('ReceiverReference', '');
        $shipment->addChild('DestinationPlaceCode', $agentId);
        $shipment->addChild('DestinationPlaceName', '');
        $shipment->addChild('PayerCode', 'S'); # Sender
        $shipment->addChild('Remarks', '');
        $shipment->addChild('ProductCode', $productCode);
        $shipment->addChild('ProductName', '');
        $shipment->addChild('Pickup', '');
        $shipment->addChild('PickupPayer', '');
        $shipment->addChild('PickupRemarks', '');
        $shipment->addChild('Delivery', '');
        $shipment->addChild('DeliveryPayer', '');
        $shipment->addChild('DeliveryRemarks', '');

        if ($this->_shouldApplyCod($request)) {
          $payment = $request->getOrderShipment()->getOrder()->getPayment();

          $shipment->addChild('CODSum', $request->getOrderShipment()->getOrder()->getGrandTotal());
          $shipment->addChild('CODCurrency', 'EUR');
          $shipment->addChild('CODAccount', $payment->getMethodInstance()->getCodIban());
          $shipment->addChild('CODBic', $payment->getMethodInstance()->getCodBic());
          $shipment->addChild('CODReference', $payment->getMethodInstance()->calculateReference($request->getOrderShipment()->getOrder()));
        }

        $shipment->addChild('Goods', '');
        $shipment->addChild('SpecialHandling', '');
        $shipment->addChild('VAKCode', '');
        $shipment->addChild('VAKDescription', '');
        $shipment->addChild('DocumentType', '');

      return $xml->asXML();
    }

    /**
     * Convert weight to kilograms
     */
    protected function _convertWeight($weight, $weightUnits) {
      if ($weightUnits != \Zend_Measure_Weight::STANDARD) {
        $weight = $this->_carrierHelper->convertMeasureWeight(
          $weight,
          $weightUnits,
          \Zend_Measure_Weight::STANDARD
        );
      }

      return $weight;
    }

    protected function _findAgentId($request) {
      $order = $request->getOrderShipment()->getOrder();
      $shippingAddress = $order->getShippingAddress();
      return $shippingAddress->getMatkahuoltoAgentId();
    }

    /**
     * Whether cash on delivery should be applied to request
     */
    protected function _shouldApplyCod($request) {
      $order = $request->getOrderShipment()->getOrder();
      $payment = $order->getPayment();
      $invoice = $order->getInvoiceCollection()->getFirstItem();

      return ($payment->getMethod() == 'bussiennakko' && empty($invoice->getID()));
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return Result|null
     */
    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        $result = $this->_trackFactory->create();

        foreach ($trackings as $tracking_number) {
          $tracking = $this->_trackStatusFactory->create();
          $tracking->setCarrier($this->_code);
          $tracking->setCarrierTitle('Matkahuolto');
          $tracking->setTracking($tracking_number);
          $tracking->setUrl($this->getTrackingUrl($tracking_number));
          $result->append($tracking);
        }

        return $result;
    }

    /**
     * Get tracking URL
     *
     * @param string $tracking_number
     * @return string
     */
    public function getTrackingUrl($tracking_number) {
      return "https://www.matkahuolto.fi/fi/seuranta/tilanne/?package_code={$tracking_number}";
    }
}
