<?php
namespace Markup\Matkahuolto\Observer;

use Markup\Matkahuolto\Model\Carrier;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigObserver implements ObserverInterface
{
    protected $carrier;
    protected $_httpClientFactory;
    protected $_storeManager;
    protected $varFactory;
    protected $configWriter;

    /**
     *
     */
    public function __construct(
      Carrier $carrier,
      \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
      \Magento\Store\Model\StoreManagerInterface $storeManager,
      \Magento\Variable\Model\VariableFactory $varFactory,
      \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
    )
    {
      $this->carrier = $carrier;
      $this->_httpClientFactory = $httpClientFactory;
      $this->_storeManager = $storeManager;
      $this->varFactory = $varFactory;
      $this->configWriter = $configWriter;
    }

    /**
     * Execute
     */
    public function execute(EventObserver $observer)
    {
      $var = $this->varFactory->create();
      $var->loadByCode('mh_enable_vak');

      $vakEnabled = !! $var->getPlainValue() ? '1' : '0';

      $this->configWriter->save('carriers/matkahuolto/vak_enabled', $vakEnabled, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);

      $this->licenseRequest();
    }

    /**
     * Send license request to the vendor
     */
    public function licenseRequest()
    {
      $domain = $this->_storeManager->getStore()->getBaseUrl();

      $url = 'http://markup.fi/licenses/request';

      $client = $this->_httpClientFactory->create();
      $client->setUri($url);
      $client->setConfig(['maxredirects' => 5, 'timeout' => 30]);
      $client->setMethod(\Zend_Http_Client::POST);
      $client->setHeaders(\Zend_Http_Client::CONTENT_TYPE, 'text/plain');
      $client->setParameterPost('product', 'Magento 2 Matkahuolto');
      $client->setParameterPost('domain', $domain);

      try {
        $client->request();
      } catch (\Exception $e) {
        // Do nothing
      }
    }
}
