define(
    [
        'ko',
        'Markup_Matkahuolto/js/view/checkout/summary/bussiennakko_fee'
    ],
    function (ko, Component) {
        'use strict';
        return Component.extend({
            isDisplayed: function () {
                return this.isFullMode();
            }
        });
    }
);
