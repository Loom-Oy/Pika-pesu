/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            var shippingAddress = quote.shippingAddress();
            if (shippingAddress['extension_attributes'] === undefined) {
                shippingAddress['extension_attributes'] = {};
            }

            shippingAddress['extension_attributes']['matkahuolto_agent_id'] = quote.matkahuoltoAgentId;

            if (quote.matkahuoltoAgentData != false && quote.matkahuoltoAgentData != null && typeof quote.matkahuoltoAgentData['id'] != 'undefined' && quote.matkahuoltoAgentData['id'] == quote.matkahuoltoAgentId) {
              shippingAddress['extension_attributes']['matkahuolto_agent_data'] = {
                agent_id: quote.matkahuoltoAgentData['id'],
                street_address: quote.matkahuoltoAgentData['street_address'],
                postcode: quote.matkahuoltoAgentData['postal_code'],
                name: quote.matkahuoltoAgentData['name'],
                city: quote.matkahuoltoAgentData['city']
              };
            }

            // pass execution to original action ('Magento_Checkout/js/action/set-shipping-information')
            return originalAction();
        });
    };
});
