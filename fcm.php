<?php
require_once 'nogit/config.php';
require_once 'config.php';

// Piqué de http://sab99r.com/blog/firebase-cloud-messaging-fcm-php-backend/
/*  
Parameter Example
	$data = array('post_id'=>'12345','post_title'=>'A Blog post');
	$target = 'single tocken id or topic name';
	or
	$target = array('token1','token2','...'); // up to 1000 in one request
*/
function real_sendMessageFCM($data,$target){
//FCM api URL
$url = 'https://fcm.googleapis.com/fcm/send';
//api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
$server_key = SERVER_FCM_TOKEN;
			
$fields = array();
$fields['data'] = $data;
if(is_array($target)){
	$fields['registration_ids'] = $target;
}else{
	$fields['to'] = $target;
}

//header with content_type api key
$headers = array(
	'Content-Type:application/json',
  'Authorization:key='.$server_key
);
			
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
$result = curl_exec($ch);
if ($result === FALSE && !HERMETIQUE) {
	echo('FCM Send Error: ' . curl_error($ch));
}
curl_close($ch);
return $result;
}

function sendMessageFCM($data, $target) {
	if (DEBUG_FCM) {
		$target_str = json_encode($target);
		$data_str = json_encode($data, JSON_PRETTY_PRINT);
		echo "\n--------------\nFCM Frame\nTargets : $target_str\nData : $data_str\n";
	}

	if (!FAKE_FCM) {
	$ret = real_sendMessageFCM($data, $target);
	if (DEBUG_FCM)
		echo "\n--------------\nFCM Answer\n$ret\n----------------------\n";
	}
}
