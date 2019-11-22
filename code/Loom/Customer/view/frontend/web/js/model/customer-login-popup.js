/*
 * @package    Loom_Customer
 * @copyright  Loom Oy - 2019
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    'use strict';

    return {
        modalWindow: null,

        /**
         * Create popUp window for provided element
         *
         * @param {HTMLElement} element
         */
        createPopUp: function (element) {
            var options = {
                'type': 'popup',
                'modalClass': 'customer-login-popup',
                'focus': '[name=username]',
                'responsive': true,
                'innerScroll': true,
                'trigger': '.trigger-ajax-login',
                'buttons': []
            };

            this.modalWindow = element;
            modal(options, $(this.modalWindow));

        },
    };
});
