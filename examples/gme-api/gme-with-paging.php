<?php
/**
 * A rough implementation of paging through the GME API to retrieve
 * multiple resultsets.
 *
 * Params:
 * Google_OAuth2 $oauthClient - An OAuth2 Client object.
 * string $url - The complete URL to call include query string params.
 * array $query_params - An array of query string params (name => value) to pass.
 * string $merge_on - Identifies the element in the response that
 *    contains the features or assets list. Typically 'features'.
 */
function gme_paging(Google_OAuth2 $oauthClient, $url, $query_params = array(), $merge_on) {
	$merged_responses = array();

	do {
		if(isset($responseObj->nextPageToken)) {
			$params["pageToken"] = $responseObj->nextPageToken;
		}

	    $start = microtime(true);
		$responseObj = json_decode(curl_wrapper($oauthClient, $url, $query_params));
	    $secs_taken = microtime(true) - $start;

		// Google imposes a max queries per second per project of 1 on most queries
		// /table/features queries are allowed higher limits as per https://developers.google.com/maps-engine/new
		// @TODO Make it smart enough to sniff for 'allowed_queries_per_second' in /tables/features responses and adapt.
	    if($secs_taken < 1) {
	      usleep(1000000 - ($secs_taken * 1000000));
	    }

		$merged_responses = array_merge($merged_responses, $responseObj->{$merge_on});
	} while(isset($responseObj->nextPageToken));

	return $merged_responses;
}
?>