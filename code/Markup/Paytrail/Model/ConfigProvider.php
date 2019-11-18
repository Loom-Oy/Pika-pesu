<?php
namespace Markup\Paytrail\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCodes = [
        Paytrail::PAYMENT_METHOD_CODE,
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper
    ) {
        $this->escaper = $escaper;
        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
        foreach ($this->methodCodes as $code) {
            $config['payment']['instructions'][$code] = $this->getInstructions($code);
            $config['payment']['use_bypass'][$code] = $this->getUseBypass($code);
            $config['payment']['method_groups'][$code] = $this->getEnabledPaymentMethodGroups($code);
            $config['payment']['payment_redirect_url'][$code] = $this->getPaymentRedirectUrl($code);
            $config['payment']['payment_template'][$code] = $this->getPaymentTemplate($code);
        }
        return $config;
    }

    /**
     * Get payment template (either normal or bypass)
     *
     * @param string $code
     * @return string
     */
    protected function getPaymentTemplate($code)
    {
      if ($this->getUseBypass($code)) {
        return 'Markup_Paytrail/payment/paytrail-bypass';
      }

      return 'Markup_Paytrail/payment/paytrail';
    }

    /**
     * Get payment redirect page URL
     *
     * @param string $code
     * @return string
     */
    protected function getPaymentRedirectUrl($code)
    {
      return $this->methods[$code]->getPaymentRedirectUrl();
    }

    /**
     * Get payment page bypass from config
     *
     * @param string $code
     * @return array
     */
    protected function getUseBypass($code)
    {
        return $this->methods[$code]->usePaymentPageBypass();
    }

    /**
     * Get payment method groups
     *
     * @param string $code
     * @return array
     */
    protected function getEnabledPaymentMethodGroups($code)
    {
        return $this->methods[$code]->getEnabledPaymentMethodGroups();
    }

    /**
     * Get instructions text from config
     *
     * @param string $code
     * @return string
     */
    protected function getInstructions($code)
    {
        return nl2br($this->escaper->escapeHtml($this->methods[$code]->getInstructions()));
    }
}
