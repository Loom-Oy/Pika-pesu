/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'paytrail',
                component: 'Markup_Paytrail/js/view/payment/method-renderer/paytrail-method'
            }
        );
        return Component.extend({});
    }
);
