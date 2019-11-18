<?php
namespace Markup\Matkahuolto\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\Data\OrderInterface;

class SalesModelServiceQuoteSubmitBefore implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        if ($order->getPayment()->getMethod() == 'bussiennakko') {
            $order->setBussiennakkoFee($quote->getBussiennakkoFee());
            $order->setBaseBussiennakkoFee($quote->getBaseBussiennakkoFee());
        }
    }
}
