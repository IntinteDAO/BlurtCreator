<?php

// Frontend
echo '<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <title>BLURT Account Creation</title>
  </head>
  <body>';


// Backend
function verify_captcha($response) {
    $data = array(
	'secret' => "",
	'response' => $response
    );

    $verify = curl_init();
    curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
    curl_setopt($verify, CURLOPT_POST, true);
    curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($verify);
    $responseData = json_decode($response);
    if($responseData->success) {
	return true;
    } else {
	return false;
    }
}

function is_already_on_blurt($nick) {
	$url = 'https://blurt-rpc.saboin.com/';
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_POSTFIELDS,'{"id":"0","jsonrpc":"2.0","method":"call","params":["condenser_api","get_account_history",["'.$nick.'",11966,1]]}');
	$data = curl_exec($curl);
	curl_close($curl);


	// Verify nickname
	$json = json_decode($data, TRUE);
	if(!empty($json['result'])) {
		return true;
	}
		return false;
}


if(!empty($_POST['username']) && !empty($_POST['posting']) && !empty($_POST['owner']) && !empty($_POST['active']) && !empty($_POST['memo'])) {
	$preg_result = preg_match('/^[a-z]{3,16}$/', $_POST['username']);
	if($preg_result==1) {
		$is_on_blurt = is_already_on_blurt($_POST['username']);
		if($is_on_blurt==0) {
			if(isset($_POST['g-recaptcha-response'])){
				$captcha=$_POST['g-recaptcha-response'];

				if(verify_captcha($captcha)==true) {
					include_once('ip_score.php');
					if(get_ip_score()==1) {
						$mysqli = new mysqli("", "", "", "");
						if ($mysqli -> connect_errno) {
							echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
							exit();
						}
						$ip = hash('sha384', isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR']);
						$email = hash('sha384', $_POST['email']);
						$verify = mysqli_fetch_array(mysqli_query($mysqli, "SELECT * from verification WHERE (ip='$ip' OR email='$email')"));
						if(empty($verify)) {
							$username = $_POST['username'];
							$posting = $_POST['posting'];
							$owner = $_POST['owner'];
							$active = $_POST['active'];
							$memo = $_POST['memo'];
							$register_result = file(""); // Sending a request to other server for creating account via HTTP protocol
							for($i=0; $i<=count($register_result)-1; $i++) {
								if (strpos($register_result[$i], 'successfully') !== false) {
									mysqli_query($mysqli, "INSERT INTO verification (email, ip) VALUES ('$email', '$ip')");
								}
								echo $register_result[$i];
							}
						} else {
							echo 'An account has already been created with this IP or Email address';
						}

					} else {
						echo 'This IP is not allowed. Most often this means it is being used by Bots, Proxies, VPNs or TOR.';
					}
		        	} else {
		        		echo 'Error received from reCaptcha server - '.$json['error-codes'][0];
		        	}
		        } else {
		    		echo 'No response from reCaptcha server, try again';
		        }


		} else {
			echo 'This user is already registered';
		}
	} else {
		echo 'This user name does not meet the criteria';
	}
} else {
	echo 'I did not detect the Login field entered.';
}

// Frontend
echo '</body><script src="libs/js/clipboard.js"></script></html>';

?>