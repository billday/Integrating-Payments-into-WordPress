<?php

/*
Plugin Name: Bill Day's PayPal GetBalance
Description: Retrieves a PayPal account balance for the currently specified username and password.
Version: 1.0
Author: Bill Day
Author URI: http://billday.com
License:  All source code is licensed under the Simplified BSD License http://www.opensource.org/licenses/bsd-license.php
*/

$billday_paypal_getbalance_environment = 'sandbox';	// or 'beta-sandbox' or 'live'

/**
 * Send HTTP POST Request
 *
 * @param	string	The API method name
 * @param	string	The POST Message fields in &name=value pair format
 * @return	array	Parsed HTTP Response body
 */
function BillDay_PayPal_GetBalance($billday_paypal_getbalance_methodName_, $billday_paypal_getbalance_nvpStr_) {
	global $billday_paypal_getbalance_environment;

	$billday_paypal_getbalance_API_UserName = urlencode('my_api_username');
	$billday_paypal_getbalance_API_Password = urlencode('my_api_password');
	$billday_paypal_getbalance_API_Signature = urlencode('my_api_signature');
	$billday_paypal_getbalance_API_Endpoint = "https://api-3t.paypal.com/nvp";
	if("sandbox" === $billday_paypal_getbalance_environment || "beta-sandbox" === $billday_paypal_getbalance_environment) {
		$billday_paypal_getbalance_API_Endpoint = "https://api-3t.$billday_paypal_getbalance_environment.paypal.com/nvp";
	}
	$billday_paypal_getbalance_version = urlencode('51.0');

	// setting the curl parameters.
	$billday_paypal_getbalance_ch = curl_init();
	curl_setopt($billday_paypal_getbalance_ch, CURLOPT_URL, $billday_paypal_getbalance_API_Endpoint);
	curl_setopt($billday_paypal_getbalance_ch, CURLOPT_VERBOSE, 1);

	// turning off the server and peer verification(TrustManager Concept).
	curl_setopt($billday_paypal_getbalance_ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($billday_paypal_getbalance_ch, CURLOPT_SSL_VERIFYHOST, FALSE);

	curl_setopt($billday_paypal_getbalance_ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($billday_paypal_getbalance_ch, CURLOPT_POST, 1);

	// NVPRequest for submitting to server
	$billday_paypal_getbalance_nvpreq = "METHOD=$billday_paypal_getbalance_methodName_&VERSION=$billday_paypal_getbalance_version&PWD=$billday_paypal_getbalance_API_Password&USER=$billday_paypal_getbalance_API_UserName&SIGNATURE=$billday_paypal_getbalance_API_Signature$billday_paypal_getbalance_nvpStr_";

	// setting the nvpreq as POST FIELD to curl
	curl_setopt($billday_paypal_getbalance_ch, CURLOPT_POSTFIELDS, $billday_paypal_getbalance_nvpreq);

	// getting response from server
	$billday_paypal_getbalance_httpResponse = curl_exec($billday_paypal_getbalance_ch);

	if(!$billday_paypal_getbalance_httpResponse) {
		exit("$billday_paypal_getbalance_methodName_ failed: ".curl_error($billday_paypal_getbalance_ch).'('.curl_errno($billday_paypal_getbalance_ch).')');
	}

	// Extract the RefundTransaction response details
	$billday_paypal_getbalance_httpResponseAr = explode("&", $billday_paypal_getbalance_httpResponse);

	$billday_paypal_getbalance_httpParsedResponseAr = array();
	foreach ($billday_paypal_getbalance_httpResponseAr as $billday_paypal_getbalance_i => $billday_paypal_getbalance_value) {
		$billday_paypal_getbalance_tmpAr = explode("=", $billday_paypal_getbalance_value);
		if(sizeof($billday_paypal_getbalance_tmpAr) > 1) {
			$billday_paypal_getbalance_httpParsedResponseAr[$billday_paypal_getbalance_tmpAr[0]] = $billday_paypal_getbalance_tmpAr[1];
		}
	}

	if((0 == sizeof($billday_paypal_getbalance_httpParsedResponseAr)) || !array_key_exists('ACK', $billday_paypal_getbalance_httpParsedResponseAr)) {
		exit("Invalid HTTP Response for POST request($billday_paypal_getbalance_nvpreq) to $billday_paypal_getbalance_API_Endpoint.");
	}

	return $billday_paypal_getbalance_httpParsedResponseAr;
}


/**
 * Displays the balance retrieved using the BDPPGetBalance function.
 */
function BillDay_PayPal_DisplayBalance() {
	$billday_paypal_getbalance_nvpStr="";

	$billday_paypal_getbalance_httpParsedResponseAr = BillDay_PayPal_GetBalance('GetBalance', $billday_paypal_getbalance_nvpStr);

	if("SUCCESS" == strtoupper($billday_paypal_getbalance_httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($billday_paypal_getbalance_httpParsedResponseAr["ACK"])) {
		exit('GetBalance Completed Successfully: '.print_r($billday_paypal_getbalance_httpParsedResponseAr, true));
	} else  {
		exit('GetBalance failed: ' . print_r($billday_paypal_getbalance_httpParsedResponseAr, true));
	}
	echo "<p id='dolly'>$billday_paypal_getbalance_httpParsedResponseAr</p>";
}


/**
 * Gets and displays the PayPal balance when the admin_footer action is called
 */
add_action('admin_footer', 'BillDay_PayPal_DisplayBalance');


/**
 * We use the same CSS to position the balance that Hello Dolly used to show the lyrics.
 */
function dolly_css() {
echo "
<style type='text/css'>
#dolly {
position: absolute;
top: 2.3em;
margin: 0;
padding: 0;
right: 10px;
font-size: 16px;
color: #d54e21;
}
</style>
";
}

add_action('admin_head', 'dolly_css');

?>