<?php
namespace Markup\Matkahuolto\Block\Adminhtml\Order\View;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order\Address;

/**
 * Matkahuolto pickup agent class
 */
class Agent extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
  /**
   * Constructor
   */
  public function __construct(
    \Magento\Backend\Block\Template\Context $context,
    \Magento\Framework\Registry $registry,
    \Magento\Sales\Helper\Admin $adminHelper,
    array $data = []
  ) {
    parent::__construct($context, $registry, $adminHelper, $data);
  }

  /**
   * Get agent
   */
  public function getAgent()
  {
    $order = $this->getOrder();
    $extAttributes = $order->getExtensionAttributes();
    $agentMethods = ['matkahuolto_80', 'matkahuolto_10', 'matkahuolto_vak'];

    if ($extAttributes && in_array($order->getShippingMethod(), $agentMethods)) {
      return $extAttributes->getMatkahuoltoAgent();
    }

    return FALSE;
  }


  /**
   * Get form action
   */
  public function getFormAction() {
    return $this->getUrl('matkahuolto/agent/update');
  }
}
