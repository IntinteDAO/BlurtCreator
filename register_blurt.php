<?php

$creator_username = '';
$creator_activekey = '';

function create_account($wif, $creator, $newAccountName, $owner, $active, $posting, $memo) {
	return shell_exec("echo \"var blurt = require('@blurtfoundation/blurtjs'); owner = '$owner'; active = '$active'; posting = '$posting'; blurt.api.setOptions({url: 'https://blurt-rpc.saboin.com'}); blurt.broadcast.accountCreate('$wif', '10.000 BLURT', '$creator', '$newAccountName', {\"weight_threshold\": 1, \"account_auths\": [], \"key_auths\": [[\"owner\", 1]]}, {\"weight_threshold\": 1, \"account_auths\": [], \"key_auths\": [[\"active\", 1]]}, {\"weight_threshold\": 1, \"account_auths\": [], \"key_auths\": [[\"posting\", 1]]}, '$memo', '{}', function(err, result) { console.log(err, result); });\" | nodejs --openssl-legacy-provider");
}

function send($from, $to, $amount, $memo, $wif) {
	return shell_exec("echo \"var blurt = require('@blurtfoundation/blurtjs'); blurt.api.setOptions({url: 'https://blurt-rpc.saboin.com'}); blurt.broadcast.transfer('$wif', '$from', '$to', '$amount', '$memo', function(err, result) { console.log(err, result); });\" | nodejs --openssl-legacy-provider");
}

$username = $_GET['username'];

$keys = array("posting" => $_GET['posting'], "owner" => $_GET['owner'], "active" => $_GET['active'], "memo" => $_GET['memo']);

echo '<div class="container"><div class="row">';
$output = str_replace(array($creator_activekey), '', nl2br(create_account($creator_activekey, $creator_username, $username, $keys['owner'], $keys['active'], $keys['posting'], $keys['memo'])));

if((strpos($output, 'signatures') !== false) && (strpos($output, 'id') !== false)) {
	echo 'Your account has been created successfully!';
	send($creator_username, $username, '3.000 BLURT', '', $creator_activekey);
} else {
	echo '<label>The account could not be created. If possible, join the <a href="https://discord.blurt.world">Blurt group on Discord</a> and write to @fervi.</label><hr>';
	echo $output;
}

echo '</div></div>';