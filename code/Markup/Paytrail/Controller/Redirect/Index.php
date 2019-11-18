<?php
namespace Markup\Paytrail\Controller\Redirect;

use \Markup\Paytrail\Model\Paytrail;
use \Magento\Framework\Exception\LocalizedException;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_jsonFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;

    /**
     * @var \Markup\Paytrail\Model\Paytrail
     */
    protected $_paytrail;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Markup\Paytrail\Model\Paytrail $paytrail
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        Paytrail $paytrail
    )
    {
        $this->urlBuilder = $context->getUrl();
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_jsonFactory = $jsonFactory;
        $this->_pageFactory = $pageFactory;
        $this->_paytrail = $paytrail;

        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
      $order = NULL;

      try {
        if ($this->getRequest()->getParam('is_ajax')) {
          // Get order
          $order = $this->_orderFactory->create();
          $order = $order->loadByIncrementId($this->_checkoutSession->getLastRealOrderId());

          $selectedPaymentMethodId = $this->getRequest()->getParam('preselected_payment_method_id');

          // Get form data
          $formData = $this->_paytrail->getFormFields($order, $selectedPaymentMethodId);
          $formAction = $this->_paytrail->getFormAction();

          // Create block containing form data
          $block = $this->_pageFactory
            ->create()
            ->getLayout()
            ->createBlock('Markup\Paytrail\Block\Redirect\Paytrail')
            ->setUrl($formAction)
            ->setParams($formData);

          $resultJson = $this->_jsonFactory->create();

          return $resultJson->setData([
            'success' => true,
            'data' => $block->toHtml()
          ]);
        }
      } catch (\Exception $e) {
        // Error will be handled below
      }

      // Something went wrong, cancel order
      if ($order) {
        $order->cancel();
        $order->addStatusHistoryComment(__('Order canceled. Failed to redirect to Paytrail.'));
        $order->save();
      }

      // Restore the quote
      $this->_checkoutSession->restoreQuote();

      $resultJson = $this->_jsonFactory->create();

      return $resultJson->setData([
        'success' => false,
      ]);
    }
}
