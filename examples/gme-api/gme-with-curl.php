<?php
/**
 * A generic wrapper for cURL that abstracts away common requirements
 * and issues around using the GME API.
 *
 * Params:
 * Google_OAuth2 $oauthClient - An OAuth2 Client object.
 * string $url - The complete URL to call include query string params.
 * array $options - An additional cURL options to pass through.
 *
 * Notably:
 * - Content-type: application/json for POST requests
 * - Catching rate limit exceeded errors
 */
function curl_wrapper(Google_OAuth2 $oauthClient, $url, $options = array()) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt_array($ch, $options);

	$headers = array(
		'Authorization: Bearer ' . $oauthClient->token["access_token"]
	);
	// Google accepts POST data as JSON only - no form-encoded input
	if(isset($options[CURLOPT_POST]) && $options[CURLOPT_POST] == true) {
		$headers[] = "Content-Type: application/json";
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$response = json_decode(curl_exec($ch));

	if(!in_array(curl_getinfo($ch, CURLINFO_HTTP_CODE), array(200, 204))) {
		// You'll want to handle errors...
	}

	if(isset($response->error) && $response->error->errors[0]->reason === "rateLimitExceeded") {
		// If you've can have multiple simultaneous clients you'll probably want to catch
		// and handle rate limit errors. So sleep for an arbitrary period...
		usleep(1000000);
		// ...and try again
	}

	curl_close($ch);
	return $response;
}
?>