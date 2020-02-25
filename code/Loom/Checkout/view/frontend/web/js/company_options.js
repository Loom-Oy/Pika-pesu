require(['jquery', 'jquery/ui'], function($){ 
    $( document ).ready(function() {

      //wait until the last element (.payment-method) being rendered
      var existCondition = setInterval(function() {
       if ($('.shipping-address').length) { 
        clearInterval(existCondition);
        showVATID();
       }
      }, 100);

      function showVATID(){
        var vatField = document.getElementsByName("shippingAddress.vat_id")[0];
        vatField.style.display = "none";
        var companyField = document.getElementsByName("company")[0];
        companyField.onkeypress = function(event) {
            vatField.style.display = "inline-block";
        };
      }

    }); 
 });