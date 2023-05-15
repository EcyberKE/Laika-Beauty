/* global location flw_payment_args jQuery*/
"use strict";

var amount = flw_payment_args.amount,
  cbUrl = flw_payment_args.cb_url,
  country = flw_payment_args.country,
  curr = flw_payment_args.currency,
  desc = flw_payment_args.desc,
  email = flw_payment_args.email,
  firstname = flw_payment_args.firstname,
  lastname = flw_payment_args.lastname,
  form = jQuery("#flw-pay-now-button"),
  p_key = flw_payment_args.p_key,
  txref = flw_payment_args.txnref,
  paymentOptions = flw_payment_args.payment_options,
  paymentStyle = flw_payment_args.payment_style,
  disableBarter = flw_payment_args.barter,
  redirect_url;

if (form) {
  form.on("click", function (evt) {
    evt.preventDefault();
    if (paymentStyle == "inline") {
      processPayment();
    } else {
      location.href = flw_payment_args.cb_url;
    }
  });
}

//switch country base on currency
switch (curr) {
  case "KES":
    country = "KE";
    break;
  case "GHS":
    country = "GH";
    break;
  case "ZAR":
    country = "ZA";
    break;
  case "TZS":
    country = "TZ";
    break;

  default:
    country = "NG";
    break;
}

var processPayment = function () {
  // setup payload
  var ravePayload = {
    amount: amount,
    country: country,
    currency: curr,
    custom_description: desc,
    customer_email: email,
    customer_firstname: firstname,
    customer_lastname: lastname,
    txref: txref,
    payment_options: paymentOptions,
    PBFPubKey: p_key,
    onclose: function () {},
    callback: function (response) {
      var tr =
        response.data.data?.txRef || response.data.transactionobject?.txRef;
      if (
        response.tx.chargeResponseCode == "00" ||
        response.tx.chargeResponseCode == "0"
      ) {
        // popup.close();
        redirectPost(cbUrl + "?txref=" + tr, response.tx);
      } else {
        alert(response.respmsg);
      }

      popup.close(); // close modal
    },
  };

  // disable barter or not
  if (disableBarter == "yes") {
    ravePayload.disable_pwb = true;
  }

  // add payload
  var popup = getpaidSetup(ravePayload);
};

var sendPaymentRequestResponse = function (res) {
  jQuery.post(cbUrl, res.tx).success(function (data) {
    var response = JSON.parse(data);
    redirect_url = response.redirect_url;
    setTimeout(redirectTo, 5000, redirect_url);
  });
};

//redirect function
var redirectPost = function (location, args) {
  // console.log(args);
  var form = "";
  jQuery.each(args, function (key, value) {
    // value = value.split('"').join('\"')
    form += '<input type="hidden" name="' + key + '" value="' + value + '">';
  });
  jQuery('<form action="' + location + '" method="POST">' + form + "</form>")
    .appendTo(jQuery(document.body))
    .submit();
};

var redirectTo = function (url) {
  location.href = url;
};
