<?php
/*
 * @package    Loom_Customer
 * @copyright  Loom Oy - 2019 
 */
namespace Loom\Customer\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Loom\Customer\Helper\Data as CustomerHelper;

class AddLoggedOutHandleObserver implements ObserverInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var CustomerHelper
     */
    private $helper;

    /**
     * AddLoggedOutHandleObserver constructor.
     *
     * @param Session $customerSession
     * @param CustomerHelper $helper
     */
    public function __construct(
        Session $customerSession,
        CustomerHelper $helper
    )
    {
        $this->customerSession = $customerSession;
        $this->helper = $helper;
    }

    /**
     * Add a custom handle responsible for adding the trigger-ajax-login class
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        if ($this->helper->isModuleEnabled()) {
            $layout = $observer->getEvent()->getLayout();
            if (!$this->customerSession->isLoggedIn()) {
                $layout->getUpdate()->addHandle('customer_customer_logged_out');
            }
        }
    }
}
