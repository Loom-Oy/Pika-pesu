<?php

/**
 * @author Loom Oy
 * @category Loom
 * @package  Loom_DLPrime
 */

namespace Loom\DLPrime\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

class AfterOrderComplete implements ObserverInterface
{
    protected $_order;
    protected $_dataHelper;
    protected $_logger;
    protected $_client;

    public function __construct(
        \Magento\Sales\Api\Data\OrderInterface $order,
        //\Loom\DLPrime\Helper\Data $_dataHelper,
        //\Loom\DLPrime\Logger\Logger $logger,
        \Zend\Http\Client $client
    ) {
        $this->_order = $order;
        //$this->_dataHelper = $_dataHelper;
        //$this->_logger = $logger;
        $this->_client = $client;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_dataHelper->isEnabled()) {

            $order = $observer->getEvent()->getOrder();

            if ($order->getStatus() == "complete") {
                $order_id = $order->getIncrementId();
                $this->_logger->info('Starting to process order with id: ' . $order_id);
                //$sessionId = $this->createRequest();
                //$request = $this->createOrder($sessionId, $order);
                //$this->createRequest($request);
                //$this->_logger->info('Order (id: ' . $order_id . ') succesfully sent to DL Prime');
                //$order->setStatus('sent_to_erp');
                //$order->save();
            }
        } elseif (!$this->_dataHelper->isEnabled()) {
            $this->_logger->info('DL Prime module is not enabled. Not sending order to ERP');
        }
    }
}
