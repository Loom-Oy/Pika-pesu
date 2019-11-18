<?php
namespace Markup\Matkahuolto\Model;

use Markup\Matkahuolto\Api\AgentsInterface;
use Magento\Framework\Xml\Security;

class Agents implements AgentsInterface
{
    protected $_xmlElFactory;
    protected $_httpClientFactory;
    protected $_request;
    protected $_xmlSecurity;
    protected $_scopeConfig;

    /**
     * Constructor
     */
    public function __construct(
      \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
      \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
      \Magento\Framework\App\Request\Http $request,
      Security $xmlSecurity,
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
      $this->_xmlElFactory = $xmlElFactory;
      $this->_httpClientFactory = $httpClientFactory;
      $this->_request = $request;
      $this->_xmlSecurity = $xmlSecurity;
      $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Returns Matkahuolto agents by postcode
     *
     * @api
     * @return string agents in JSON
     */
    public function agents() {
      $params = $this->_request->getParams();
      $postcode = isset($params['postcode']) ? $params['postcode'] : '';
      $method = isset($params['method']) ? $params['method'] : '';

      $xmlAgents = $this->requestAgents($postcode, $method);

      $agents = array();
      if ($xmlAgents !== FALSE) {
        foreach ($xmlAgents as $agent) {
          $distance = (float) str_replace(',', '.', (string) $agent->Distance);
          $label = "{$agent->Name} ({$distance} km)";
          $agents[] = array(
            'label' => $label,
            'type' => (string) $agent->Type,
            'name' => (string) $agent->Name,
            'street_address' => (string) $agent->StreetAddress,
            'postal_code' => (string) $agent->PostalCode,
            'city' => (string) $agent->City,
            'country' => (string) $agent->Country,
            'mh_office' => (string) $agent->MhOffice,
            'id' => (string) $agent->Id,
            'shop_id' => (string) $agent->ShopId,
            'distance' => $distance,
          );
        }
      }

      return json_encode($agents);
    }

    private function requestAgents($postcode, $method) {
      $requestXml = $this->requestBody($postcode, $method);

      $mode = $this->_scopeConfig->getValue('carriers/matkahuolto/mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
      $apiVersion = $this->_scopeConfig->getValue('carriers/matkahuolto/office_search_api', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

      if ($apiVersion == 'v2') {
        if ($mode) {
          $url = 'https://extservices.matkahuolto.fi/noutopistehaku/public/searchoffices';
        } else {
          $url = 'https://extservicestest.matkahuolto.fi/noutopistehaku/public/searchoffices';
        }
      } else {
        $url = 'http://map.matkahuolto.fi/map24mh/searchoffices';
      }

      $client = $this->_httpClientFactory->create();
      $client->setUri($url);
      $client->setConfig(['maxredirects' => 5, 'timeout' => 10]);
      $client->setMethod(\Zend_Http_Client::POST);
      $client->setHeaders(\Zend_Http_Client::CONTENT_TYPE, 'text/xml');
      $client->setRawData($requestXml, 'text/xml');

      $response = $client->request();

      if ($response != FALSE) {
        $body = $response->getBody();
        $xmlResponse = $this->parseXml(urldecode($body));

        if (!isset($xmlResponse->ErrorNbr)) {
          return $xmlResponse;
        }
      }

      return FALSE;
    }

    private function requestBody($postcode, $method) {
      $xml = $this->_xmlElFactory->create(['data' => '<MHSearchOfficesRequest></MHSearchOfficesRequest>']);

      $userId = $this->_scopeConfig->getValue('carriers/matkahuolto/userid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

      $xml->addChild('Login', $userId);
      $xml->addChild('Version', '1.7');
      $xml->addChild('StreetAddress', '');
      $xml->addChild('PostalCode', $postcode);

      $officeType = FALSE;
      if ( ! empty($method) && $method == 'vak') {
        $vakOfficeType = $this->_scopeConfig->getValue('carriers/matkahuolto/office_type_vak', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        switch ($vakOfficeType) {
          case 't':
            $officeType = 'T';
            break;
          case 'm':
            $officeType = 'M';
            break;
        }
      } else if ( ! empty($method) && $method == '10') {
        // M = Matkahuollon omat toimipisteet (bussipaketit)
        // T = Lähellä paketin noutopisteet (lähikaupat)
        $officeType = 'M';
      }

      if ($officeType) {
        $xml->addChild('OfficeType', $officeType);
      }

      $xml->addChild('MaxResults', '20');

      return $xml->asXML();
    }

    /**
     * Parse XML string and return XML document object or false
     *
     * @param string $xmlContent
     * @param string $customSimplexml
     * @return \SimpleXMLElement|bool
     * @throws LocalizedException
     *
     * @api
     */
    private function parseXml($xmlContent, $customSimplexml = 'SimpleXMLElement') {
      if (!$this->_xmlSecurity->scan($xmlContent)) {
        throw new LocalizedException(__('Security validation of XML document has been failed.'));
      }

      $xmlElement = simplexml_load_string($xmlContent, $customSimplexml);

      return $xmlElement;
    }
}
