<?php
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'SimpleXMLElement_Addl.php');

function load_xml($url,$log_file_abs=null){
global $config;
if(!$log_file_abs){$log_file_abs = $config['log_file_absolute'];}
//$login = 'bestdealers';
//$password = 't$Gm$,xt5SHg';
//$url = 'http://'.$login.':'.$password.'@'.'tech-bg.com/files/bestdealers/manufacturers.xml';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
//	curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
	$result = curl_exec($ch);
	$info = curl_getinfo($ch);
	if(curl_errno($ch)){
		if(!$log_file_abs){echo '['.curl_errno($ch).'] '.curl_error($ch)."\n";}
		else{file_put_contents($log_file_abs, date("Y-m-d | h:i:sa ").
			'Curl error:' . curl_errno($ch) . ' ' . curl_error($ch) . "\n", FILE_APPEND);}
	}elseif(parse_url($url, PHP_URL_SCHEME)==='http' && $info['http_code']!==200){
		if(!$log_file_abs){echo 'http code: '.(int)$info['http_code']."\n";}
		else{file_put_contents($log_file_abs, date("Y-m-d | h:i:sa ").
			'http code: '.(int)$info['http_code']."\n", FILE_APPEND);}
	}elseif(empty($result)){
		if(!$log_file_abs){echo "Empty XML.\n";}
		else{file_put_contents($log_file_abs, date("Y-m-d | h:i:sa ").
			"Empty XML.\n", FILE_APPEND);}
	}
	curl_close($ch);
	//print_r($info,false);
	//echo($result);

	libxml_use_internal_errors(true);
	$xml = simplexml_load_string($result,"SimpleXMLElement_Addl");
	if(!$xml){
		if(!$log_file_abs){echo "Failed loading ".$url."\n";}
		else{file_put_contents($log_file_abs, date("Y-m-d | h:i:sa ").
			"Failed loading ".$url."\n", FILE_APPEND);}
    		foreach(libxml_get_errors() as $error){
		if(!$log_file_abs){echo "\t".$error->message."\n";}
		else{file_put_contents($log_file_abs, date("Y-m-d | h:i:sa ").
			"\t".$error->message."\n", FILE_APPEND);}
    		}		
	}
	return $xml;
}

//test
//print_r(load_xml('http://bestdealers:t$Gm$,xt5SHg@tech-bg.com/files/bestdealers/manufacturers.xml'),false);
?>
