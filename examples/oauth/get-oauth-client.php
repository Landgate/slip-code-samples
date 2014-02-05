<?php
// Your Google Developer project details from https://cloud.google.com/console
const SERVICE_APP_NAME = 'App Name';
const SERVICE_CLIENT_ID = 'Your Client ID';
const SERVICE_ACCOUNT_EMAIL = 'Your Account Email';
const KEY_FILE = 'Your Private Key File';
const SCOPE = 'https://www.googleapis.com/auth/mapsengine.readonly'; // sans .readonly for write operations

/**
 * A simplified version of Google's OAuth for PHP Web Server flow.
 * https://developers.google.com/maps-engine/documentation/oauth/webserver
 *
 * Implements:
 * - Retrieval of access tokens
 * - Refresh of an existing access token
 * - Storage of access tokens in sessions
 *
 * Requirements:
 *   - Google APIs Client Libraries: https://developers.google.com/discovery/libraries
 */
$oauthClient = new Google_OAuth2();
$client = new Google_Client();
$client->setApplicationName(SERVICE_APP_NAME);
$client->setClientId(SERVICE_CLIENT_ID);

$token_name = "oauth_client_" . $client_id . "_token";
$token = (array)json_decode($_SESSION[$token_name]);

if(isset($token["access_token"]) && time() <= ($token["created"] + $token["expires_in"])) {
	$oauthClient->setAccessToken($_SESSION[$token_name]);
} else {
	$oauthClient->refreshTokenWithAssertion(new Google_AssertionCredentials(
	  SERVICE_ACCOUNT_EMAIL,
	  array(SCOPE),
	  file_get_contents(KEY_FILE)
	));
	$_SESSION[$token_name] = $oauthClient->getAccessToken();
}

if($oauthClient->getAccessToken()) {
	// All good, we've got a valid access token!
} else {
	// Uh oh :(
}
?>