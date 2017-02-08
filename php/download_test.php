<?php
$login = 'bestdealers';
$password = 't$Gm$,xt5SHg';
$url = 'http://'.$login.':'.$password.'@'.'tech-bg.com/files/bestdealers/manufacturers.xml';
//$url = 'http://tech-bg.com/files/bestdealers/manufacturers.xml';
//$url = 'file:///home/bitrix/techvision/config.ini';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
//curl_setopt($ch, CURLOPT_USERPWD, "$login:$password");
$result = curl_exec($ch);
$info = curl_getinfo($ch);
if(curl_errno($ch))
{
	echo 'Curl errno: ' . curl_errno($ch);echo"\n";
	echo 'Curl error: ' . curl_error($ch);echo"\n";
}
curl_close($ch);
print_r($info,false);
echo($result);
?>
