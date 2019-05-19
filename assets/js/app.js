var apiKey = "brs-1234567890"; // TODO REPLACE WITH YOUR OWN API KEY

var apiUrl = "https://browsymous.example.com:8080/api";
var apiUrl3ds = "http://browsymous.example.com:8080/na";

function subscribe(offerId, subscriber, paymentCard, successCallback, failureCallback) {
    var requestUrl = apiUrl + "/subscription/start";
    console.log("subscribe: " + requestUrl);

    var offer = {"id": offerId};
    var requestObject = {
        "offer": offer,
        "subscriber": subscriber,
        "paymentCard": paymentCard
    };

    var requestData = JSON.stringify(requestObject);
    console.log("requestData: " + requestData);

    $.ajax({
        url: requestUrl,
        type: 'POST',
        dataType: 'json',
        data: requestData,
        contentType: 'application/json',
        mimeType: 'application/json',
        headers: {
            "Authorization": "Apikey " + apiKey
        }
    }).done(successCallback).fail(failureCallback);
}

function subscribe3ds(subscriptionPlanId, subscriber, paymentCard, successCallback, failureCallback) {
    var requestUrl = apiUrl3ds + "/subscription/start/3ds";
    console.log("subscribe: " + requestUrl);

    var subscriptionPlan = {"id": subscriptionPlanId};
    var requestObject = {
        "subscriptionPlan": subscriptionPlan,
        "subscriber": subscriber,
        "paymentCard": paymentCard
    };

    var requestData = JSON.stringify(requestObject);
    console.log("requestData: " + requestData);

    $.ajax({
        url: requestUrl,
        type: 'POST',
        dataType: 'json',
        data: requestData,
        contentType: 'application/x-www-form-urlencoded',
        mimeType: 'application/json',
        accept: 'text/html',
    }).done(successCallback).fail(failureCallback);
}

function getActiveSubscriptions(subscriber, successCallback, failureCallback){
    var requestUrl = apiUrl + "/subsriptions/subscriber/" + subscriber;
    console.log("getActiveSubscriptions: " + apiUrl);

    $.ajax({
        url: requestUrl,
        type: 'GET',
        dataType: 'json',
        contentType: 'application/json',
        mimeType: 'application/json',
        headers: {
            "Authorization": "Apikey " + apiKey
        }
    }).done(successCallback).fail(failureCallback);

}

function getSubscriptionPlans(apiUrl, apiKey, successCallback, failureCallback) {
    var requestUrl = apiUrl + "/subscription-plans";
    console.log("api url: " + apiUrl);

    $.ajax({
        url: requestUrl,
        type: 'GET',
        dataType: 'json',
        contentType: 'application/json',
        mimeType: 'application/json',
        headers: {
            "Authorization": "Apikey " + apiKey
        }
    }).done(successCallback).fail(failureCallback);
}

function getSubscriptionPlan(subscriptionPlanId, successCallback, failureCallback) {
    var requestUrl = apiUrl + "/subscription-plans/" + subscriptionPlanId;
    console.log("api url: " + apiUrl);

    $.ajax({
        url: requestUrl,
        type: 'GET',
        dataType: 'json',
        contentType: 'application/json',
        mimeType: 'application/json',
        headers: {
            "Authorization": "Apikey " + apiKey
        }
    }).done(successCallback).fail(failureCallback);
}

function qs(key) {
    key = key.replace(/[*+?^$.\[\]{}()|\\\/]/g, "\\$&"); // escape RegEx meta chars
    var match = location.search.match(new RegExp("[?&]"+key+"=([^&]+)(&|$)"));
    return match && decodeURIComponent(match[1].replace(/\+/g, " "));
}



