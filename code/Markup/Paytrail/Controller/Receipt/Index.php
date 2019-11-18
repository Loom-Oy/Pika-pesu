<?php
namespace Markup\Paytrail\Controller\Receipt;

use Magento\Sales\Model\Order\Payment\Transaction;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;

    /**
     * Success constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $session,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
    )
    {
        parent::__construct($context);

        $this->urlBuilder = $context->getUrl();
        $this->session = $session;
        $this->transactionRepository = $transactionRepository;
        $this->orderFactory = $orderFactory;
        $this->orderSender = $orderSender;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
      // Check order number
      $orderNo = $this->getRequest()->getParam('ORDER_NUMBER');
      if (empty($orderNo)) {
        $this->session->restoreQuote();
        $this->messageManager->addError(__('Order number is empty'));
        $this->_redirect('checkout/cart');
        return;
      }

      // Get order
      // Instead of loading order by using session->getLastRealOrderId(), we load by
      // Paytrail supplied order ID. This is because we do not have cookies
      // available if Paytrail server requests this page instead of the customer.
      // This will also mitigate order ID tampering attempts.
      $order = $this->orderFactory->create()->loadByIncrementId($orderNo);
      if (!$order->getId()) {
        $this->session->restoreQuote();
        $this->messageManager->addError(__('No order for processing found'));
        $this->_redirect('checkout/cart');
        return;
      }

      // Unset Paytrail redirect URL
      $this->session->unsPaytrailRedirectUrl();

      /** @var \Magento\Payment\Model\Method\AbstractMethod $method */
      $method = $order->getPayment()->getMethodInstance();

      $isE2Payment = (bool) $this->getRequest()->getParam('e2');

      if ($isE2Payment) {
        $verifiedPayment = $method->verifyPaymentE2(
          $this->getRequest()->getParam('ORDER_NUMBER'),
          $this->getRequest()->getParam('PAYMENT_ID'),
          $this->getRequest()->getParam('AMOUNT'),
          $this->getRequest()->getParam('CURRENCY'),
          $this->getRequest()->getParam('PAYMENT_METHOD'),
          $this->getRequest()->getParam('TIMESTAMP'),
          $this->getRequest()->getParam('STATUS'),
          $this->getRequest()->getParam('SETTLEMENT_REFERENCE_NUMBER'),
          $this->getRequest()->getParam('RETURN_AUTHCODE')
        );
      } else {
        $verifiedPayment = $method->verifyPayment(
          $this->getRequest()->getParam('ORDER_NUMBER'),
          $this->getRequest()->getParam('TIMESTAMP'),
          $this->getRequest()->getParam('PAID'),
          $this->getRequest()->getParam('METHOD'),
          $this->getRequest()->getParam('RETURN_AUTHCODE')
        );
      }

      if (!$verifiedPayment) {
        // Cancel order
        $order->cancel();
        $order->addStatusHistoryComment(__('Order canceled. Failed to complete the payment.'));
        $order->save();

        // Restore the quote
        $this->session->restoreQuote();

        $this->messageManager->addError(__('Failed to complete the payment. Please try again or contact the customer service.'));

        $this->_redirect('checkout/cart');
        return;
      }

      if ($isE2Payment) {
        $transactionId = $this->getRequest()->getParam('PAYMENT_ID');
      } else {
        $transactionId = $this->getRequest()->getParam('PAID');
      }

      // Check if transaction is already registered
      $transaction = $this->transactionRepository->getByTransactionId(
        $transactionId,
        $order->getPayment()->getId(),
        $order->getId()
      );

      if ($transaction) {
        $details = $transaction->getAdditionalInformation(Transaction::RAW_DETAILS);
        if (is_array($details)) {
          // Redirect to Success Page
          $this->session->getQuote()->setIsActive(false)->save();
          $this->_redirect('checkout/onepage/success');
          return;
        }

        // Restore the quote
        $this->session->restoreQuote();

        $this->messageManager->addError(__('Payment failed'));
        $this->_redirect('checkout/cart');
      }

      // Register transaction
      $order->getPayment()->setTransactionId($transactionId);

      if ($isE2Payment) {
        $details = array(
          'orderNo' => $this->getRequest()->getParam('ORDER_NUMBER'),
          'amount' => $this->getRequest()->getParam('AMOUNT'),
          'currency' => $this->getRequest()->getParam('CURRENCY'),
          'method' => $this->getRequest()->getParam('PAYMENT_METHOD'),
          'timestamp' => $this->getRequest()->getParam('TIMESTAMP'),
          'status' => $this->getRequest()->getParam('STATUS'),
          'settlementReference' => $this->getRequest()->getParam('SETTLEMENT_REFERENCE_NUMBER'),
          'authcode' => $this->getRequest()->getParam('RETURN_AUTHCODE')
        );
      } else {
        $details = array(
          'orderNo' => $this->getRequest()->getParam('ORDER_NUMBER'),
          'timestamp' => $this->getRequest()->getParam('TIMESTAMP'),
          'paid' => $this->getRequest()->getParam('PAID'),
          'method' => $this->getRequest()->getParam('METHOD'),
          'authcode' => $this->getRequest()->getParam('RETURN_AUTHCODE'),
        );
      }

      $transaction = $method->addPaymentTransaction($order, $details);

      // Set last transaction ID
      $order->getPayment()->setLastTransId($transactionId)->save();

      // Create invoice
      if ($order->canInvoice()) {
        $method->getInfoInstance()->capture();

        // Add transaction ID for invoice so we can make online refunds
        $invoice = $method->getInfoInstance()->getCreatedInvoice();
        if ($invoice) {
          $invoice->setTransactionId($order->getPayment()->getLastTransId());
          $invoice->save();
        }
      }

      // Change order status
      $newStatus = $method->getConfigData('order_status');
      $status = $method->getState($newStatus);
      $order->setData('state', $status->getState());
      $order->setStatus($status->getStatus());
      $order->addStatusHistoryComment(__('Payment has been completed'));
      $order->save();

      // Send order notification
      try {
        $this->orderSender->send($order);
      } catch (\Exception $e) {
        $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
      }

      // Redirect to success page
      $this->session->getQuote()->setIsActive(false)->save();
      $this->_redirect('checkout/onepage/success');
    }
}
