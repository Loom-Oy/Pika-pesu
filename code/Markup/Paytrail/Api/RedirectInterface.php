<?php
namespace Markup\Paytrail\Api;

interface RedirectInterface
{
    /**
     * Returns Paytrail redirect URL
     *
     * @api
     * @return string redirect URL
     */
    public function redirect();
}
