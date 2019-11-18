<?php
namespace Markup\Matkahuolto\Controller\Adminhtml\Agent;

class Update extends \Magento\Backend\App\Action
{
	protected $request;
	protected $orderRepository;
	protected $agentFactory;
	protected $urlBuilder;

	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
		\Markup\Matkahuolto\Model\Order\MatkahuoltoAgentFactory $agentFactory,
		\Magento\Framework\App\Request\Http $request
	) {
		$this->request = $request;
		$this->orderRepository = $orderRepository;
		$this->agentFactory = $agentFactory;
		$this->urlBuilder = $context->getUrl();

		return parent::__construct($context);
	}

	public function execute()
	{
		$params = $this->getRequest()->getPostValue();

		if (isset($params['matkahuolto_agent']) && isset($params['order_id'])) {
			$order = $this->orderRepository->get($params['order_id']);

			$shippingAddress = $order->getShippingAddress();
			if ($shippingAddress) {
				$shippingAddress->setMatkahuoltoAgentId($params['matkahuolto_agent']['id']);
				$agent = $this->agentFactory->create();
				$agent->setAgentId($params['matkahuolto_agent']['id']);
				$agent->setName($params['matkahuolto_agent']['name']);
				$agent->setStreetAddress($params['matkahuolto_agent']['street_address']);
				$agent->setPostcode($params['matkahuolto_agent']['postal_code']);
				$agent->setCity($params['matkahuolto_agent']['city']);
				$shippingAddress->setMatkahuoltoAgentData($agent);
				$shippingAddress->save();
			}
		}

		$this->messageManager->addSuccess(__('Pickup location updated.'));
		$orderUrl = $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $params['order_id']]);
		$this->_redirect($orderUrl);
	}
}
