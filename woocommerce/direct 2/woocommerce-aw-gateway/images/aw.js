var zjscb = function(data) {
  $form = jQuery("form.checkout");
  if (data.r == 1) {
    // Successfully tokenized
    jQuery('#aw-token').val(data.token);
    jQuery("#aw-card-cvc").attr("name", "aw-card-cvc"); // Send the CVV, so it can be forwarded to the gateway.
    $form.submit();
  } else if (data.r == 97) {
    // Failed, validation error
    var error_message = "Credit Card Validation error: <br />\r\n";
    jQuery(data["errors[]"]).each(function() {
      error_message += " - " + this + "<br />\r\n";
    });

    $form.find('.payment-errors').html(error_message);
    $form.unblock();
  } else {
    $form.find(".payment-errors").html("Unknown Error (Gateway code: " + data.r + "). Please Try Again.");
    $form.unblock();
  }
}

jQuery('form.checkout').on('change', '#wc-aw-cc-form input[type=text]', function() {
  jQuery('#aw-token').val('');
});

jQuery("form.checkout").bind("checkout_place_order_aw", function(e) {
  var $form = jQuery(this);
  if (jQuery("#aw-token").val().length !== 0) return true;

  $form.find('.payment-errors').html('');
  $form.block({message: null,overlayCSS: {background: "#fff url(" + woocommerce_params.ajax_loader_url + ") no-repeat center",backgroundSize: "16px 16px",opacity: .6}});

  var fieldVal = function(name) {
    return jQuery('#aw-' + name).val();
  };

  var expiryMonth = function() {
    return fieldVal('card-expiry').replace(" ", "").split("/")[0];
  };

  var expiryYear = function() {
    return parseInt(fieldVal('card-expiry').replace(" ", "").split("/")[1]) + 2000;
  };

  jQuery.ajax({
    type: "GET",
    url: aw.url,
    data: {"card_holder": jQuery("#billing_first_name").val() + " " + jQuery("#billing_last_name").val(), "card_number": fieldVal('card-number').replace(" ", ""), "expiry_month": expiryMonth(), "expiry_year": expiryYear(), "cvv": fieldVal('card-cvc'), "verification": aw.verification_value, "return_path": aw.return_path},
    jsonpCallback: "zjscb",
    contentType: "text/javascript",
    dataType: "jsonp",
    error: function(e) {
      // Handle errors here - all non HTTP 2xx errors, such as HTTP 500, 403, 401 etc
      // (very unlikely, but HTTP 500 or HTTP 502 is the most likely to happen)
      jQuery.find('.payment-errors').text('Error submitting credit card: ' + e.message);
      $form.unblock();
    }
  });
  return false;
});
