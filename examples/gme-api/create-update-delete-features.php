<?php
/**
 * Demonstration of the create, update, and delete portion of the GME API.
 *
 * Creates a table, inserts features, modifies a feature, and deletes features.
 * More info:
 * - https://developers.google.com/maps-engine/documentation/reference/v1/tables/create
 * - https://developers.google.com/maps-engine/documentation/reference/v1/tables/features/batchInsert
 * - https://developers.google.com/maps-engine/documentation/reference/v1/tables/features/batchPatch
 * - https://developers.google.com/maps-engine/documentation/reference/v1/tables/features/batchDelete
 *
 * Dependencies:
 * - cURL: http://www.php.net/manual/en/curl.installation.php
 * - Google APIs Client Library for PHP: https://code.google.com/p/google-api-php-client/
 */
require_once 'gme-api-utils.inc';
require_once 'libs/google-api-php-client-0.6.7/src/Google_Client.php';

// configuration
const SERVICE_APP_NAME = 'App Name';
const SERVICE_CLIENT_ID = 'Your Client ID';
const SERVICE_ACCOUNT_EMAIL = 'Your Account Email';
const KEY_FILE = 'Your Private Key File';
const SCOPE = 'https://www.googleapis.com/auth/mapsengine';
const PROJECT_ID = "09372590152434720789"; // SLIP Future's projectId

// OAuth handshake with Google - establish a new access token or retreive the existing one from our session
$oc = get_oauth_client(SERVICE_APP_NAME, SERVICE_CLIENT_ID, SERVICE_ACCOUNT_EMAIL, KEY_FILE, SCOPE);

// Create an empty vector point datasource
list($response, $curl_info) = curl_wrapper("https://www.googleapis.com/mapsengine/v1/tables", array(
	CURLOPT_POST => true,
	CURLOPT_POSTFIELDS => json_encode(array(
		"projectId" => PROJECT_ID,
		"name" => "SLIP Future Code Sample Test",
		"draftAccessList" => "Landgate (Public Editor)",
		"schema" => array(
			"columns" => array(
				array("name" => "geometry", "type" => "points"),
				array("name" => "id", "type" => "integer"),
				array("name" => "name", "type" => "string")
			)
		)
	))
));

$table = json_decode($response);
if(isset($table->id)) {
	echo 'Table Created: OK<br>';
}
// Wait a moment for the new table to propagate through Google's data centres
sleep(10);


// Feature count check
list($features, $curl_info) = curl_wrapper("https://www.googleapis.com/mapsengine/v1/tables/" . $table->id . "/features");
echo "Feature Count: " . count(json_decode($features)->features) . "<br>";


// Insert a couple of features
list($response, $curl_info) = curl_wrapper("https://www.googleapis.com/mapsengine/v1/tables/" . $table->id . "/features/batchInsert", array(
	CURLOPT_POST => true,
	CURLOPT_POSTFIELDS => json_encode(array(
		"features" => array(
			array(
				"type" => "Feature",
				"geometry" => array(
					"type" => "Point",
					"coordinates" => array("115.852", "-31.944")
				),
				"properties" => array(
					"gx_id" => "1",
					"id" => "1",
					"name" => "Rockface, Northbridge"
				)
			),
			array(
				"type" => "Feature",
				"geometry" => array(
					"type" => "Point",
					"coordinates" => array("115.921", "-31.911")
				),
				"properties" => array(
					"gx_id" => "2",
					"id" => "2",
					"name" => "The Hangout, Bayswater"
				)
			)
		),
	))
));
if(strlen($response) === 0) {
	echo "Features Inserted: OK<br>";
}


// Feature count check
list($features, $curl_info) = curl_wrapper("https://www.googleapis.com/mapsengine/v1/tables/" . $table->id . "/features");
echo "Feature Count: " . count(json_decode($features)->features) . "<br>";


// Check featureId 1's name
list($features, $curl_info) = curl_wrapper("https://www.googleapis.com/mapsengine/v1/tables/" . $table->id . "/features?where=" . urlencode("id=1"));
echo "Feature #1 Name: " . json_decode($features)->features[0]->properties->name.'<br>';


// Modify one of the features
list($response, $curl_info) = curl_wrapper("https://www.googleapis.com/mapsengine/v1/tables/" . $table->id . "/features/batchPatch", array(
	CURLOPT_POST => true,
	CURLOPT_POSTFIELDS => json_encode(array(
		"features" => array(
			array(
				"type" => "Feature",
				"properties" => array(
					"gx_id" => "1",
					"name" => "Rockface Indoor Rock Climing Centre, Northbridge"
				)
			)
		),
	))
));
if(strlen($response) === 0) {
	echo "Features Updated: OK<br>";
}


// Check that featureId 1's name changed
list($features, $curl_info) = curl_wrapper("https://www.googleapis.com/mapsengine/v1/tables/" . $table->id . "/features?where=" . urlencode("id=1"));
echo "Feature #1 Name: " . json_decode($features)->features[0]->properties->name.'<br>';


// Delete both features
list($response, $curl_info) = curl_wrapper("https://www.googleapis.com/mapsengine/v1/tables/" . $table->id . "/features/batchDelete", array(
	CURLOPT_POST => true,
	CURLOPT_POSTFIELDS => json_encode(array(
		"gx_ids" => array(1, 2),
	))
));
if(strlen($response) === 0) {
	echo "Features Deleted: OK<br>";
}


// Feature count check
list($features, $curl_info) = curl_wrapper("https://www.googleapis.com/mapsengine/v1/tables/" . $table->id . "/features");
echo "Feature Count: " . count(json_decode($features)->features) . "<br>";
?>