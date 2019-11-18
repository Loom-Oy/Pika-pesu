<?php
namespace Markup\Matkahuolto\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SetEmailVariables implements ObserverInterface
{
  private $_quoteRepository;

  public function __construct(\Magento\Quote\Model\QuoteRepository $_quoteRepository)
  {
      $this->_quoteRepository = $_quoteRepository;
  }

  public function execute(EventObserver $observer)
  {
    $sender = $observer->getEvent()->getData('sender');
    $transport = $observer->getEvent()->getData('transport');
    $order = $transport->getData('order');
    $quote = $this->_quoteRepository->get($order->getQuoteId());

    if ( ! $order->getIsVirtual() && $quote ) {
      list($carrier, $shippingMethod) = explode('_', $order->getShippingMethod());
      if ($carrier === 'matkahuolto') {
        // Get agent data
        $shippingAddress = $quote->getShippingAddress();
        $agentData = $shippingAddress->getMatkahuoltoAgentData();
        if ($agentData) {
          $agent = unserialize($agentData);

          // Set agent data
          $transport->setData('matkahuoltoAgentId', $agent->getAgentId());
          $transport->setData('matkahuoltoAgentName', $agent->getName());
          $transport->setData('matkahuoltoAgentAddress', $agent->getStreetAddress());
          $transport->setData('matkahuoltoAgentPostcode', $agent->getPostcode());
          $transport->setData('matkahuoltoAgentCity', $agent->getCity());
        }
      }
    }

    return $this;
  }
}
