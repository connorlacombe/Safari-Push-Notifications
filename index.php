<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

require ("mysqli.inc.php");

if(!function_exists('apache_request_headers')) {
	function apache_request_headers() {
	  $arh = array();
	  $rx_http = '/\AHTTP_/';
	  foreach($_SERVER as $key => $val) {
	    if( preg_match($rx_http, $key) ) {
	      $arh_key = preg_replace($rx_http, '', $key);
	      $rx_matches = array();
	      // do some nasty string manipulations to restore the original letter case
	      // this should work in most cases
	      $rx_matches = explode('_', $arh_key);
	      if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
	        foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
	        $arh_key = implode('-', $rx_matches);
	      }
	      $arh[$arh_key] = $val;
	    }
	  }
	  return( $arh );
	}
}

$path = parse_url($_SERVER['REQUEST_URI']);
$path = explode("/", substr($path["path"], 1));
$version = $path[0];
$function = $path[1];

if ($function == "pushPackages") { //Build and output push package to Safari
	$body = @file_get_contents("php://input");
	$body = json_decode($body, true);
	global $id;
	$id = $body["id"];

	// return pushPackage

	include ("createPushPackage.php");

	$package_path = create_push_package();
	if (empty($package_path)) {
		http_response_code(500);
		die;
	}
	
	header("Content-type: application/zip");
	echo file_get_contents($package_path);
	die;
}
else if ($function == "devices") { // safari is adding or deleting the device
	$id = "";
	foreach(apache_request_headers() as $header=>$value) { // this is the authorization key we packaged in the website.json pushPackage
		if($header == "AUTHORIZATION") {
			$value = explode("_", $value);
			$id = $value[1];
			break;
		}
	}
	$token = $path[2];
	if ($_SERVER['REQUEST_METHOD'] == "POST") { //Adding
		$r = mysqli_do("SELECT * FROM push WHERE id='$id'");
		if (mysqli_num_rows($r) == 0) {
			mysqli_do("INSERT INTO push (id, token) VALUES ('$id', '$token')");
		}
	}
	else if ($_SERVER['REQUEST_METHOD'] == "DELETE") { //Deleting
		mysqli_do("DELETE FROM push WHERE id='$id' LIMIT 1");
	}
}
else if ($function == "verifyCode") { //function for the mobile page of the demo
	$id = $path[2];
	$r = mysqli_do("SELECT * FROM push WHERE id='$id'");
	if(mysqli_num_rows($r) > 0) {
		echo("valid");
	}
	else {
		echo ("invalid");
	}
}
else if ($function == "push") { //pushes a notification
	$title = $_REQUEST["title"];
	$body = $_REQUEST["body"];
	$button = $_REQUEST["button"];
	$id = $path[2];
	$r = mysqli_do("SELECT * FROM push WHERE id='$id'");
	$r = mysqli_fetch_assoc($r);
	$deviceToken = $r["token"];
	$payload['aps']['alert'] = array(
		"title" => $title,
		"body" => $body,
		"action" => $button
	);
	$payload['aps']['url-args'] = array(
		"clicked"
	);
	$payload = json_encode($payload);
	$apnsHost = 'gateway.push.apple.com';
	$apnsPort = 2195;
	$apnsCert = 'apns-cert.pem';
	$streamContext = stream_context_create();
	stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
	$apns = stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
	$apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $deviceToken)) . chr(0) . chr(strlen($payload)) . $payload;
	fwrite($apns, $apnsMessage);
	fclose($apns);
}
else { // just other demo-related stuff
	if($path[0] == "clicked") {
		include ("click.html");
	}
	else if (stristr($_SERVER['HTTP_USER_AGENT'], "iphone") === false) {
		if($_SERVER["HTTPS"] != "on") {
		   header("HTTP/1.1 301 Moved Permanently");
		   header("Location: https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
		   exit();
		}
		include ("desktop.php");
	}
	else if(stristr($_SERVER['HTTP_USER_AGENT'], "iphone") !== false) {
		include ("mobile.html");
	}
}

?>
