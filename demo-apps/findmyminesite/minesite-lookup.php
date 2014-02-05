<?php
/**
 * Dependencies:
 * - cURL: http://www.php.net/manual/en/curl.installation.php
 * - Google APIs Client Library for PHP: https://code.google.com/p/google-api-php-client/
 */
require_once 'code-samples/gme-api-utils.inc';
require_once 'code-samples/libs/google-api-php-client-0.6.7/src/Google_Client.php';

// configuration
const SERVICE_APP_NAME = 'App Name';
const SERVICE_CLIENT_ID = 'Your Client ID';
const SERVICE_ACCOUNT_EMAIL = 'Your Account Email';
const KEY_FILE = 'Your Private Key File';
const SCOPE = 'https://www.googleapis.com/auth/mapsengine.readonly';
const MINEDEX_TABLE_ID = "09372590152434720789-18111015997902175456";

$oauthClient = get_oauth_client(SERVICE_APP_NAME, SERVICE_CLIENT_ID, SERVICE_ACCOUNT_EMAIL, KEY_FILE, SCOPE);

$lat = (float)$_GET["lat"];
$lng = (float)$_GET["lng"];
$filter = http_build_query(array(
	"select" => "site_code,short_name,commodity,target_com,site_stage,site_type_,site_sub_t,geometry,ST_DISTANCE(geometry,ST_POINT(" . $lng . "," . $lat . ")) AS distance",
	"where" => "ST_DISTANCE(geometry,ST_POINT(" . $lng . "," . $lat . "))<=5000"
));
$gme_url = "https://www.googleapis.com/mapsengine/v1/tables/" . MINEDEX_TABLE_ID . "/features?" . $filter;

$context = array("http" =>
  array(
    "method" => "get",
    "header" => 'Authorization: Bearer ' . $oauthClient->token["access_token"],
  )
);
$context = stream_context_create($context);
$features = file_get_contents($gme_url, false, $context);

header("Content-type: application/json");
echo $features;
?>