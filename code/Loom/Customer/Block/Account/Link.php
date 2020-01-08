<?php
/*
 * @package    Loom_Customer
 * @copyright  Loom Oy - 2019
 */
namespace Loom\Customer\Block\Account;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Http\Context;
use Magento\Framework\View\Element\Template;
use Magento\Customer\Block\Account\SortLinkInterface;

class Link extends Template implements SortLinkInterface
{
    /** @var Url $_customerUrl */
    protected $_customerUrl;

    /** @var Context $httpContext */
    protected $httpContext;

    /**
     * Links constructor.
     * @param Template\Context $context
     * @param array $data
     * @param Url $customerUrl
     * @param Context $httpContext
     */
    public function __construct(Template\Context $context,
                                Url $customerUrl,
                                Context $httpContext,
                                array $data)
    {
        $this->_customerUrl = $customerUrl;
        $this->httpContext = $httpContext;

        parent::__construct($context, $data);
    }

    /**
     * Is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * Get account URL
     * 
     * @return string
     */
    public function getAccountUrl()
    {
        return $this->_customerUrl->getAccountUrl();
    }

    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
