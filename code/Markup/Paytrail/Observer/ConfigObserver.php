<?php
namespace Markup\Paytrail\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ConfigObserver implements ObserverInterface
{
    protected $_httpClientFactory;
    protected $_storeManager;

    /**
     *
     */
    public function __construct(
      \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
      \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
      $this->_httpClientFactory = $httpClientFactory;
      $this->_storeManager = $storeManager;
    }

    /**
     * Send license key to the vendor
     */
    public function execute(EventObserver $observer)
    {
      //$domain = $this->_storeManager->getStore()->getBaseUrl();
      $domain = 'https://pika-pesu.fi';

      $url = 'http://markup.fi/licenses/request';

      $client = $this->_httpClientFactory->create();
      $client->setUri($url);
      $client->setConfig(['maxredirects' => 5, 'timeout' => 30]);
      $client->setMethod(\Zend_Http_Client::POST);
      $client->setHeaders(\Zend_Http_Client::CONTENT_TYPE, 'text/plain');
      $client->setParameterPost('product', 'Magento 2 Paytrail');
      $client->setParameterPost('domain', $domain);

      $client->request();
    }
}
