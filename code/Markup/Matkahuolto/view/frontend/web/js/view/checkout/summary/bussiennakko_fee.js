/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'ko',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (ko, Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Markup_Matkahuolto/checkout/summary/bussiennakko_fee'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
            isDisplayed: function() {
                return this.isFullMode();
            },
            hasTotal: function () {
                if (this.totals()) {
                    return !!totals.getSegment('bussiennakko_fee');
                }

                return false;
            },
            getValue: function() {
                var price = 0;
                if (this.hasTotal()) {
                    price = totals.getSegment('bussiennakko_fee').value;
                }
                return this.getFormattedPrice(price);
            },
            getBaseValue: function() {
                var price = 0;
                if (this.hasTotal()) {
                    price = totals.getSegment('bussiennakko_fee').value;
                }
                return this.getFormattedPrice(price);
            },
            shouldDisplay: function () {
                var price = 0;
                if (this.hasTotal()) {
                    price = totals.getSegment('bussiennakko_fee').value;
                }

                return price;
            }
        });
    }
);
