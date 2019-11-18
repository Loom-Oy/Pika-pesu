/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'jquery',
        'mage/storage',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/model/url-builder',
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/totals'
    ],
    function (ko, $, storage, Component, quote, getTotalsAction, urlBuilder, mageUrlBuilder, fullScreenLoader, customer, totals) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Markup_Matkahuolto/payment/bussiennakko'
            },
            lastDetectedMethod: null,
            getInstructions: function() {
                return window.checkoutConfig.payment.instructions[this.item.method];
            },
            refreshMethod: function () {
                var serviceUrl;

                var paymentData = quote.paymentMethod();

                if (paymentData['method'] === 'free') {
                    // get copy of paymentData object, so we don't change the original
                    paymentData = JSON.parse(JSON.stringify(paymentData));
                    delete paymentData['title'];
                }

                fullScreenLoader.startLoader();

                if (customer.isLoggedIn()) {
                    serviceUrl = urlBuilder.createUrl('/carts/mine/selected-payment-method', {});
                } else {
                    serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/selected-payment-method', {
                        cartId: quote.getQuoteId()
                    });
                }

                var payload = {
                    cartId: quote.getQuoteId(),
                    method: paymentData
                };

                return storage.put(
                    serviceUrl, JSON.stringify(payload)
                ).done(function () {
                    getTotalsAction([]);
                    fullScreenLoader.stopLoader();
                });
            },
            initObservable: function () {
                this._super();
                var me = this;

                quote.paymentMethod.subscribe(function() {
                    if (quote.paymentMethod() !== null && quote.paymentMethod().method != me.lastDetectedMethod) {
                        if (
                            (quote.paymentMethod().method == 'bussiennakko') ||
                            (me.lastDetectedMethod == 'bussiennakko') ||
                            (totals.getSegment('bussiennakko_fee') && (me.lastDetectedMethod === null))
                        ) {
                            me.refreshMethod();
                        }

                        me.lastDetectedMethod = quote.paymentMethod().method;
                    }
                });

                return this;
            }
        });
    }
);
