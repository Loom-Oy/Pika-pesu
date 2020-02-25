<?php

namespace Loom\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session;


class CompanyInvoice implements ObserverInterface
{
    private $checkoutSession;

    public function __construct(
        Session $_checkoutSession) {
        $this->checkoutSession = $_checkoutSession;
    }
    /**
     * payment_method_is_active event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // If payment method is check / money order (lasku) and current quote is not company customer
        if (($observer->getEvent()->getMethodInstance()->getCode() == "checkmo") && empty($this->checkoutSession->getQuote()->getShippingAddress()->getCompany())) {
            $checkResult = $observer->getEvent()->getResult();
            $checkResult->setData('is_available', false); //this is disabling the payment method at checkout page
        }
    }
}