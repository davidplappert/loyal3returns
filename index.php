<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// set a $email and $password variable in config.php and include it here
require_once("config.php");
require_once("simple_html_dom.php");

$login_url = "https://www.loyal3.com/login";
$transaction_url = "https://www.loyal3.com/accounts/transactions/index";

function login()
{
	global $login_url, $email, $password;
	$fields = array();

	$html = curler($url = $login_url);
	$html = str_get_html($html);

	$validationInputs = $html->find("form#login input[value='true']");
	foreach ($validationInputs as $vi)
	{
		$fields[$vi->attr['name']] = 'true';
	}

	$usernameInput = $html->find("form#login input#username");
	$fields[$usernameInput[0]->attr['name']] = $email;

	$passwordInput = $html->find("form#login input#password");
	$fields[$passwordInput[0]->attr['name']] = $password;


	$submitName = $html->find('form#login input#submit');
	$fields[$submitName[0]->attr['name']] = $submitName[0]->attr['value'];

	//this will take the field names and cookies from the login form, and preform the login for us
	$preformLogin = curler($url = $login_url, $fields = $fields);
}


function curler($url,$fields = FALSE,$useCookies = FALSE)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_REFERER, "http://davidplappert.com/");
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	//attach the fields if there are any
	if (is_array($fields)){
		$fields_string = '';
		foreach($fields as $key=>$value) {
			$fields_string .= $key.'='.urlencode($value).'&';
		}
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		$useCookies = TRUE;
	}
	if ($useCookies){
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
	}else{
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
	}
	// Download the given URL, and return output
    $output = curl_exec($ch);
	//Look For Curl Errors
	if (curl_errno($ch)){
		echo 'Curl error: '.curl_error($ch).', '.curl_errno($ch);
		return FALSE;
	}
	/*
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	echo $httpCode;
	*/
	// Close the cURL resource, and free system resources
    curl_close($ch);
    return $output;
}


function getTransactions()
{
	$transFile = checkCache();
	if(!$transFile){
		$transFile = refreshCache();
	}


}

function checkCache()
{
	return FALSE;
}

function refreshCache()
{
	login();
	unlink('cookie.txt');
}

echo "<pre>";

getTransactions();

?>
