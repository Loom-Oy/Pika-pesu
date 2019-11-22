/*
 * @package    Loom_Customer
 * @copyright  Loom Oy - 2019
 */

define([
    'jquery',
    'ko',
    'Magento_Ui/js/form/form',
    'Loom_Customer/js/action/login',
    'Magento_Customer/js/customer-data',
    'Loom_Customer/js/model/customer-login-popup',
    'mage/translate',
    'mage/url',
    'mage/validation'
], function ($, ko, Component, loginAction, customerData, customer, $t, url) {
    'use strict';

    return Component.extend({
        registerUrl: window.customer.customerRegisterUrl,
        forgotPasswordUrl: window.customer.customerForgotPasswordUrl,
        autocomplete: window.customer.autocomplete,
        modalWindow: null,
        isLoading: ko.observable(false),

        defaults: {
            template: 'Loom_Customer/customer-login-popup'
        },

        /**
         * Init
         */
        initialize: function () {
            var self = this;

            this._super();
            this._resetStyleCss();
            url.setBaseUrl(window.customer.baseUrl);
            loginAction.registerLoginCallback(function () {
                self.isLoading(false);
            });
        },

        /** Init popup login window */
        setAjaxModelElement: function (element) {
            if (customer.modalWindow == null) {
                customer.createPopUp(element);
            }
        },

        /** Is login form enabled for current customer */
        isActive: function () {
            var customer = customerData.get('customer');

            return customer() == false; //eslint-disable-line eqeqeq
        },

        /** Show login popup window */
        showModal: function () {
            if (this.modalWindow) {
                $(this.modalWindow).modal('openModal');
                self._setStyleCss(self.options.innerWidth);
            }
        },

        /**
         * Provide login action
         *
         * @return {Boolean}
         */
        login: function (formUiElement, event) {
            var loginData = {},
                formElement = $(event.currentTarget),
                formDataArray = formElement.serializeArray();

            event.stopPropagation();
            event.preventDefault();

            formDataArray.forEach(function (entry) {
                loginData[entry.name] = entry.value;
            });

            if (formElement.validation() &&
                formElement.validation('isValid')
            ) {
                this.isLoading(true);
                loginAction(loginData);
            }

            return false;
        },

        /**
         * Set width of the popup
         * @private
         */
        _setStyleCss: function(width) {
            width = width || 400;
            if (window.innerWidth > 786) {
                this.element.parent().parent('.modal-inner-wrap').css({'max-width': width+'px'});
            }
        },

        /**
         * Reset width of the popup
         * @private
         */
        _resetStyleCss: function() {
            var self = this;
            $( window ).resize(function() {
                if (window.innerWidth <= 786) {
                    self.element.parent().parent('.modal-inner-wrap').css({'max-width': 'initial'});
                } else {
                    self._setStyleCss(self.options.innerWidth);
                }
            });
        }
    });
});
