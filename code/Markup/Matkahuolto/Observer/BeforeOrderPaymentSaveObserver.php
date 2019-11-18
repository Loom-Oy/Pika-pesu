<?php
/**
 * Bussiennakko Observer
 */
namespace Markup\Matkahuolto\Observer;

use Magento\Framework\Event\ObserverInterface;
use Markup\Matkahuolto\Model\Bussiennakko;

class BeforeOrderPaymentSaveObserver implements ObserverInterface
{
    /**
     * Sets current instructions for bussiennakko
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $observer->getEvent()->getPayment();
        if ($payment->getMethod() == Bussiennakko::PAYMENT_METHOD_CODE) {
            $payment->setAdditionalInformation(
                'instructions',
                $payment->getMethodInstance()->getInstructions()
            );
        }
    }
}
