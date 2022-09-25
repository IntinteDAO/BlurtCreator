<?php

function get_ip_score() {
$ipscore_key = '';

if(empty($ipscore_key)) { return true; }

/*
* Retrieve the user's IP address. 
* You could also pull this from another source such as a database.
* 
*/
$ip = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR'];


// Retrieve additional (optional) data points which help us enhance fraud scores.
$user_agent = $_SERVER['HTTP_USER_AGENT']; 
$user_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

// Set the strictness for this query. (0 (least strict) - 3 (most strict))
$strictness = 1;

// You may want to allow public access points like coffee shops, schools, corporations, etc...
$allow_public_access_points = 'true';

// Reduce scoring penalties for mixed quality IP addresses shared by good and bad users.
$lighter_penalties = 'false';

// Create parameters array.
$parameters = array(
    'user_agent' => $user_agent,
    'user_language' => $user_language,
    'strictness' => $strictness,
    'allow_public_access_points' => $allow_public_access_points,
    'lighter_penalties' => $lighter_penalties
);

/* User & Transaction Scoring
* Score additional information from a user, order, or transaction for risk analysis
* Please see the documentation and example code to include this feature in your scoring:
* https://www.ipqualityscore.com/documentation/proxy-detection/transaction-scoring
* This feature requires a Premium plan or greater
*/

// Format Parameters
$formatted_parameters = http_build_query($parameters);

// Create API URL
$url = sprintf(
    'https://www.ipqualityscore.com/api/json/ip/%s/%s?%s', 
    $ipscore_key,
    $ip, 
    $formatted_parameters
);

// Fetch The Result
$timeout = 5;

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);

$json = curl_exec($curl);
curl_close($curl);

// Decode the result into an array.
$result = json_decode($json, true);

// Check to see if our query was successful.
	if(isset($result['success']) && $result['success'] === true){
		if($result['fraud_score'] >= 80 || $result['tor'] === true || $result['proxy'] === true || $result['vpn'] === true) {
			return false;
		} else {
			return true;
		}

	}

}