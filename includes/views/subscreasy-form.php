<?php
/**
 * HTML output for the subscription form
 */

// Getting user data to display and pass in for the subscription
$user = wp_get_current_user();
$userData = get_userdata($user->ID);

$first_name = $userData->first_name == "" ? $user->user_login : $userData->first_name;
$last_name = $userData->last_name;
$email = $userData->user_email;
$phone = get_user_meta($user->ID, 'user_phone', "")[0];
$secureID = get_user_meta($user->ID, 'user_secureID', "")[0];

$options = get_option( 'subscreasy', array() );

if ( ! is_user_logged_in() ) {
    // Redirect to home page if the user is not logged in
    wp_redirect( get_home_url() );
}

// Check if the cookie is set for offerID
if ( isset( $_REQUEST[ 'offer_id' ] ) ) {
    $offerID = $_REQUEST[ 'offer_id' ];
}
else if ( isset( $_COOKIE['offerID'] ) ) {
    $offerID = $_COOKIE['offerID'];
}
else if ( isset( $_COOKIE['subscreasy_offer'] ) ) {
    $offerID = $_COOKIE['subscreasy_offer'];
}
else {
    // Redirect to home page if the cookie is not set
    wp_redirect( get_home_url() );
}

// API URL.
$api_url = 'production' === $options['environment'] ? 'https://prod.subscreasy.com/api/offers/' . $offerID : 'https://sandbox.subscreasy.com/api/offers/' . $offerID;

// HTTP headers.
$headers  = array(
    'Accept: application/json, text/plain, */*',
    'Authorization: Apikey ' . $options['api_key'],
);

/*
 * cURL request.
 */
$ch = curl_init();

// cURL options.
curl_setopt( $ch, CURLOPT_HTTPHEADER,     $headers );
curl_setopt( $ch, CURLOPT_URL,            $api_url );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

$response = curl_exec( $ch );

curl_close( $ch );

// Get offer data
$offer = json_decode($response);

if (!$offer) {
    get_header();
    echo "<h1>Product not found!</h1>";
    echo "<p>Go to <a href='" . get_home_url() . "'>home page.</a></p>";
    get_footer();
    exit();
}

//print_r($offer);

$serviceType = strtolower($offer->productType);

$companyName = $options['site_name'];
$callbackURL = $options['callback_url'];

// Encode the card number
$card_number = rawurlencode($card_number);

$countries_api_url = "https://restcountries.eu/rest/v2/all";
/*
 * cURL request.
 */
$ch = curl_init();

// cURL options.
curl_setopt( $ch, CURLOPT_HTTPHEADER,     $headers );
curl_setopt( $ch, CURLOPT_URL,            $countries_api_url );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

$response = curl_exec( $ch );

curl_close( $ch );

// Get offer data
$countries = json_decode($response);

get_header();

/*
 * Display the user data, offer data and the form
 */
?>

<div class="container">
    <div>
        <form role="form" id="payment-form" method="POST" action="https://sandbox.subscreasy.com/na/subscription/start/4ds" onsubmit="displayLoadingSpinner()">
            <div class="row">
                <div class="col-xs-12 col-md-8">
                    <!--SUBSCRIBER DETAILS STARTS HERE-->
                    <div class="card bg-light mb-2" style="max-width: 30rem;">
                        <div class="card-body">
                            <h5 class="mb-0"><span data-translate="subscriber">Subscriber Information</span> <i class="fa fa-user" aria-hidden="true"></i></h5>
                            <div class="form-group row">
                                <div class="col-xs-12 col-md-6"><label class="form-control-label" for="name" data-translate="name">Name</label>
                                    <input id="name" class="form-control ng-untouched ng-pristine ng-valid" type="text" autocomplete="name" required="" name="subscriber.name" value="<?php echo $first_name; ?>" >
                                </div>
                                <div class="col-xs-12 col-md-6"><label class="form-control-label" for="surname" data-translate="surname">Surname</label>
                                    <input id="surname" class="form-control ng-untouched ng-pristine ng-valid" type="text" autocomplete="family-name" required="" name="subscriber.surname" value="<?php echo $last_name; ?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-xs-12 col-md-6"><label class="form-control-label" for="email" data-translate="email">Email</label>
                                    <input readonly class="form-control ng-untouched ng-pristine ng-valid" type="text"  name="subscriber.email" value="<?php echo $email; ?>">
                                </div>
                                <div class="col-xs-12 col-md-6"><label class="form-control-label" for="phone" data-translate="phone">Phone</label>
                                    <input id="phone" class="form-control ng-untouched ng-pristine ng-valid" type="text" required="" name="subscriber.phoneNumber" value="<?php echo $phone; ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="privacyPolicyUrl" name="privacyPolicyUrl" required="" value="true"><input type="hidden" name="_privacyPolicyUrl" value="on">
                                        <a data-translate="privacyPolicyUrl" target="_blank" href="https://www.subscreasy.com/gizlilik-ve-guvenlik-politikasi/">I agree that I have read the Privacy Policy.</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--SUBSCRIBER DETAILS ENDS HERE-->

                    <br>
                    <br>

                    <?php
                    if ( strcmp( $serviceType, "physical") == 0 ):
                        ?>
                        <!-- Shipping Information Starts Here -->
                        <div class="card bg-light  mb-2" style="max-width: 30rem;">
                            <div class="card-body">
                                <h5 class="mb-0"><span data-translate="shipping">Shipping Information</span></h5>
                                <div class="form-group row">
                                    <div class="col-xs-12 col-md-12">
                                        <label class="form-control-label" for="shippingAddress_name" data-translate="shippingName">Name, Surname</label>
                                        <input class="form-control" type="text" name="subscriber.shippingAddress.name" id="shippingAddress_name" autocomplete="given-name" required="" value="" >
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-12">
                                        <label class="form-control-label" for="shippingAddress_address" data-translate="shippingAddress">Shipping Address</label>
                                        <input class="form-control" type="text" name="subscriber.shippingAddress.streetAddress" id="shippingAddress_address" autocomplete="address-line1" required="" value="">

                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-xs-12 col-md-4">
                                        <label class="form-control-label" for="shippingAddress_city" data-translate="shippingCity">City</label>
                                        <input class="form-control" type="text" name="subscriber.shippingAddress.city" id="shippingAddress_city" autocomplete="address-level2" required="" value="">
                                    </div>

                                    <div class="col-xs-12 col-md-4">
                                        <label class="form-control-label" for="shippingAddress_country" data-translate="shippingCountry">Country</label>
                                        <select class="form-control" name="subscriber.shippingAddress.country" id="shippingAddress_country" autocomplete="country-name" required="">
                                            <option value=""></option>
                                            <?php
                                            foreach ( $countries as $country ) {
                                                echo "<option value='$country->name'>$country->name</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-xs-12 col-md-4">
                                        <label class="form-control-label" for="shippingAddress_zipCode" data-translate="shippingZipCode">ZIP Code</label>
                                        <input class="form-control" type="text" name="subscriber.shippingAddress.postalCode" id="shippingAddress_zipCode" autocomplete="postal-code" required="" value="" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Shipping Information Ends Here -->
                    <?php
                    endif;
                    ?>

                    <!--RADIO BUTTONS-->
                    <div>
                        <label class="mr-3">
                            <input type="radio" value="CC" id="paymentType1" name="paymentType" checked="checked"> <span data-translate="creditCard">Credit Card</span>
                        </label>
                        <label>
                            <input type="radio" value="OFFLINE" id="paymentType2" name="paymentType"> <span data-translate="wireTransfer">Wire Transfer</span>
                        </label>
                    </div>
                    <!--RADIO BUTTONS-->

                    <!--WIRE TRANSFERS(HAVALE) FORM STARTS HERE-->
                    <div id="wireTransfersForm" class="row" style="display: none;">
                        <br>
                        <div class="col-md-4 col-sm-6">
                            <div class="card bg-light mb-4">
                                <h5 class="card-header"><?php _e('a bank', 'subscreasy'); ?></h5>
                                <div class="card-body">
                                    <div>
                                        <small>
                                            <span data-translate="branch"><?php _e('Branch', 'subscreasy'); ?></span>: <span></span><br>
                                        </small>
                                        <small>
                                            <span data-translate="Iban"><?php _e('Iban', 'subscreasy'); ?></span>: <span><?php _e('tr111111111111', 'subscreasy'); ?></span><br>
                                        </small>
                                        <small>
                                            <span></span> -
                                            <span></span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--WIRE TRANSFERS(HAVALE) FORM ENDS HERE-->
                    <!-- CREDIT CARD FORM STARTS HERE -->
                    <div>
                        <div class="card credit-card-box bg-light" style="max-width: 30rem;" id="creditCardForm">
                            <div class="card-body">
                                <h6 class="mb-0"><span class="panel-title-text" data-translate="creditCardInformation"><?php _e('Card Information', 'subscreasy'); ?></span> <i class="fa fa-lock" aria-hidden="true"></i></h6>
                                <div class="form-row">
                                    <div class="col-12">
                                        <div class="form-group"><label for="cardHolderName" data-translate="cardHolderName"><?php _e('Card Holder Name', 'subscreasy'); ?></label>
                                            <div class="input-group">
                                                <input class="form-control" type="text" id="cardHolderName"  required="" placeholder="" name="paymentCard.cardHolderName" value="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-12">
                                        <div class="form-group"><label for="cardNumber" data-translate="cardNumber"><?php _e('Card Number', 'subscreasy'); ?></label>
                                            <div class="input-group">
                                                <input class="form-control" id="cardNumber" required="" autocomplete="cc-number" name="paymentCard.cardNumber" value="">
                                                <div class="input-group-append"><span class="input-group-text"><i class="fa fa-credit-card"></i></span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="expireMonth" data-translate="expireMonth"><?php _e('Month', 'subscreasy'); ?></label>
                                            <select class="form-control" id="expireMonth" required="" autocomplete="cc-exp" name="paymentCard.expireMonth">
                                                <option value=""></option>
                                                <option value="01">01</option>
                                                <option value="02">02</option>
                                                <option value="03">03</option>
                                                <option value="04">04</option>
                                                <option value="05">05</option>
                                                <option value="06">06</option>
                                                <option value="07">07</option>
                                                <option value="08">08</option>
                                                <option value="09">09</option>
                                                <option value="10">10</option>
                                                <option value="11">11</option>
                                                <option value="12">12</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="expireYear" data-translate="expireYear"><?php _e('Year', 'subscreasy'); ?></label>
                                            <select class="form-control" id="expireYear" required="" autocomplete="cc-exp" name="paymentCard.expireYear">
                                                <option value=""></option>
                                                <option value="2019">2019</option>
                                                <option value="2020">2020</option>
                                                <option value="2021">2021</option>
                                                <option value="2022">2022</option>
                                                <option value="2023">2023</option>
                                                <option value="2024">2024</option>
                                                <option value="2025">2025</option>
                                                <option value="2026">2026</option>
                                                <option value="2027">2027</option>
                                                <option value="2028">2028</option>
                                                <option value="2029">2029</option>
                                                <option value="2030">2030</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-5 pull-right">
                                        <div class="form-group">
                                            <label for="cvc" class="text-truncate" data-translate="cvc"><?php _e('Security Code', 'subscreasy'); ?></label>
                                            <input class="form-control" id="cvc" required="" placeholder="CVC" autocomplete="cc-csc" maxlength="3" name="paymentCard.cvc" value=""></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- CREDIT CARD FORM ENDS HERE -->

                    <br>
                </div>

                <div class="col-xs-12 col-md-4">
                    <!--SUBSCRIPTION DETAILS STARTS HERE-->
                    <div class="card text-muted">
                        <div class="card-header">
                            <h5 class="mb-0" data-translate="subscription"><?php _e('Subscription Details', 'subscreasy'); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row"><h6 class="col-md-12"><?php echo $offer->name; ?></h6></div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6"><h6><i data-translate="total"><?php _e('Total', 'subscreasy'); ?></i></h6></div>
                                <div class="col-md-6 pull-right"><h5 class="pull-right"><?php echo $offer->price . ' ' . $offer->currency ; ?></h5></div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <img src="<?php echo SUBSCREASY_ROOT_URL . '/assets/images/mastercard-visa-americanexpress-discovernetwork-pci.png'; ?>" style="max-width: 100%; height: auto;">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-md-8">
                    <!--PAYMENT BUTTON-->
                    <div class="form-row">
                        <br>
                        <div class="col-md-12 text-center">
                            <label id="errorPlaceholder"></label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-4 offset-md-4 ">
                            <button class="btn btn-primary btn-block btn-md" type="submit">
                                <span id="price" data-price="<?php echo $offer->price . ' ' . $offer->currency ; ?>" ><?php _e('Pay', 'subscreasy'); ?> <?php echo $offer->price . ' ' . $offer->currency ; ?></span> <span id="loadingSpinner" style="display: none"><i class="fa fa-spinner fa-spin" style="font-size:24px"></i></span></button></div>
                    </div>
                    <!--PAYMENT BUTTON-->

                    <br>
                </div>
            </div>

            <input type="hidden" name="productType" value="<?php echo strtoupper($serviceType); ?>">
            <input type="hidden" name="editable" value="true">
            <input type="hidden" name="paymentType" value="CC">
            <input type="hidden" name="subscriber.secureId" value="<?php echo $secureID; ?>">
            <input type="hidden" name="offer.id" value="<?php echo $offerID; ?>">
            <input type="hidden" name="companySiteName" value="<?php echo $companyName; ?>">
            <input type="hidden" name="callbackUrl" value="<?php echo $callbackURL; ?>">


        </form>
    </div>

    <br>

</div>

<!--    <script type="text/javascript" src="--><?php //echo SUBSCREASY_ROOT_URL . '/assets/js/app.js'; ?><!--"></script>-->
<script>
    /*<![CDATA[*/
    if ($('input[name=paymentType]:checked').val() === "OFFLINE") {
        $("#creditCardForm").hide();
        $("#wireTransfersForm").show();
        inputDisabled();
    } else {
        $("#creditCardForm").show();
        $("#wireTransfersForm").hide();
        inputRemoveDisabled();
    }

    $(document).ready(function () {
        $('input[name=paymentType]').change(function() {
            if (this.value === 'CC') {
                $("#wireTransfersForm").slideToggle("slow", () => $("#creditCardForm").slideToggle("slow"));
                inputRemoveDisabled();
            } else if (this.value === 'OFFLINE') {
                $("#creditCardForm").slideToggle("slow", () => $("#wireTransfersForm").slideToggle("slow"));
                inputDisabled();
            }
        });
    });

    function inputRemoveDisabled() {
        $("#cardHolderName").removeAttr('disabled');
        $("#cardNumber").removeAttr('disabled');
        $("#expireMonth").removeAttr('disabled');
        $("#expireYear").removeAttr('disabled');
        $("#cvc").removeAttr('disabled');
    }

    function inputDisabled() {
        $("#cardHolderName").attr('disabled', 'disabled');
        $("#cardNumber").attr('disabled', 'disabled');
        $("#expireMonth").attr('disabled', 'disabled');
        $("#expireYear").attr('disabled', 'disabled');
        $("#cvc").attr('disabled', 'disabled');
    }

    function cc_format(value) {
        var v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        var matches = v.match(/\d{4,16}/g);
        var match = matches && matches[0] || '';
        var parts = [];
        for (i=0, len=match.length; i<len; i+=4) {
            parts.push(match.substring(i, i+4));
        }
        if (parts.length) {
            return parts.join(' ');
        } else {
            return value;
        }
    }

    $("#cardNumber").keypress(function (e) {
        if ((e.which < 48 || e.which > 57) && (e.which !== 8) && (e.which !== 0)) {
            return false;
        }

        return true;
    });

    onload = function() {
        document.getElementById('cardNumber').oninput = function() {
            this.value = cc_format(this.value);
        }
    };

    function displayLoadingSpinner() {
        document.getElementById('loadingSpinner').style.display = "inline";
    }

    var processButton = $("#price");
    var price = processButton.data('price');
    var langs = ['tr', 'en'];
    var current_lang = navigator.language;
    current_lang = current_lang === undefined ? 'en' : current_lang;
    if (current_lang.indexOf('tr') !== -1) {
        current_lang = 'tr';
    } else {
        current_lang = 'en';
    }
    window.change_lang = function(index) {
        current_lang = langs[index];
        translate();
        processButton.text(parametricTranslate('processButton' ,price));
    };

    function translate() {
        $("[data-translate]").each(function(){
            var key = $(this).data('translate');
            $(this).html(dictionary[key][current_lang] || "N/A");
        });

    }
    translate();

    String.prototype.format = function() {
        var a = this;
        for (var k in arguments) {
            a = a.replace("{" + k + "}", arguments[k])
        }
        return a;
    };

    function parametricTranslate(key, value) {
        var translatedValue = dictionary[key][current_lang];
        return translatedValue.format(value);
    }

    processButton.text(parametricTranslate('processButton' ,price));
    /*]]>*/
</script>

<?php

get_footer();

?>
