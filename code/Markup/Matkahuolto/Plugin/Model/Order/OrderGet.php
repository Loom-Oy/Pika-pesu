<?php

namespace Markup\Matkahuolto\Plugin\Model\Order;

class OrderGet
{
  private $orderExtensionFactory;
  private $matkahuoltoAgentFactory;

  /**
   * Init plugin
   */
  public function __construct(
    \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory,
    \Markup\Matkahuolto\Model\Order\MatkahuoltoAgentFactory $matkahuoltoAgentFactory
  ) {
    $this->orderExtensionFactory = $orderExtensionFactory;
    $this->matkahuoltoAgentFactory = $matkahuoltoAgentFactory;
  }

  public function afterGet(
    \Magento\Sales\Api\OrderRepositoryInterface $subject,
    \Magento\Sales\Api\Data\OrderInterface $resultOrder
  ) {
    $resultOrder = $this->getMatkahuoltoAgent($resultOrder);

    return $resultOrder;
  }

  private function getMatkahuoltoAgent(\Magento\Sales\Api\Data\OrderInterface $order)
  {
    if ($order->getIsVirtual()) {
      return $order;
    }

    $extensionAttributes = $order->getExtensionAttributes();
    $shippingAddress = $order->getShippingAddress();

    if ($shippingAddress) {
      $matkahuoltoAgentData = $shippingAddress->getMatkahuoltoAgentData();
      if (!empty($matkahuoltoAgentData)) {
        $matkahuoltoAgent = unserialize($matkahuoltoAgentData);

        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();
        $orderExtension->setMatkahuoltoAgent($matkahuoltoAgent);
        $order->setExtensionAttributes($orderExtension);
      }
    }

    return $order;
  }
}
