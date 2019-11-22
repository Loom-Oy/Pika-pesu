<?php
/*
 * @package    Loom_Customer
 * @copyright  Loom Oy - 2019
 */
namespace Loom\Customer\Plugin;

use Magento\Customer\Model\Context;
use Loom\Customer\Helper\Data as CustomerHelper;

class ModifySignInHrefPlugin
{
    /**
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var CustomerHelper
     */
    protected $helper;

    /**
     * ModifySignInHrefPlugin constructor.
     *
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param CustomerHelper $helper
     */
    public function __construct(
        \Magento\Framework\App\Http\Context $httpContext,
        CustomerHelper $helper
    )
    {
        $this->httpContext = $httpContext;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Customer\Block\Account\AuthorizationLink $subject
     * @param $result
     * @return string
     */
    public function afterGetHref(\Magento\Customer\Block\Account\AuthorizationLink $subject, $result)
    {
        if ($this->helper->isModuleEnabled()) {
            if (!$this->isLoggedIn()) {
                $result = '#';
            }
        }
        return $result;
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH);
    }
}
