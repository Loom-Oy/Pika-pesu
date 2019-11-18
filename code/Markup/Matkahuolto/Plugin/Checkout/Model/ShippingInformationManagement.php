<?php
namespace Markup\Matkahuolto\Plugin\Checkout\Model;
class ShippingInformationManagement
{
    protected $quoteRepository;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
      $address = $addressInformation->getShippingAddress();
      $extAttributes = $address->getExtensionAttributes();

      if ($extAttributes) {
        $agentId = $extAttributes->getMatkahuoltoAgentId();
        $agentData = $extAttributes->getMatkahuoltoAgentData();
        $address->setMatkahuoltoAgentId($agentId);
        $address->setMatkahuoltoAgentData($agentData);
      }
    }
}
