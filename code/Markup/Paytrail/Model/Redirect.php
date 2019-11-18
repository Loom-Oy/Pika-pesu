<?php
namespace Markup\Paytrail\Model;

use Markup\Paytrail\Api\RedirectInterface;
use \Magento\Checkout\Model\Session;

class Redirect implements RedirectInterface
{
    protected $_checkoutSession;

    /**
     * Constructor
     */
    public function __construct(
      Session $checkoutSession
    ) {
      $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Returns Paytrail redirect URL
     *
     * @api
     * @return string redirect URL
     */
    public function redirect() {
      $url = $this->_checkoutSession->getPaytrailRedirectUrl();

      if ($url) {
        return $url;
      }

      return FALSE;
    }
}
