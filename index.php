<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// set a $email and $password variable in config.php and include it here
require_once("config.php");
require_once("simple_html_dom.php");

$login_url = "https://www.loyal3.com/login";
$transaction_url = "https://www.loyal3.com/accounts/transactions/index";
$filename = $email.'.cache';

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

function getTransactions()
{
	global $transaction_url;
	$html = curler($url = $transaction_url);
	return $html;
}

function curler($url,$fields = FALSE)
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
	}
	if (file_exists('cookie.txt')){
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
	}
	curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
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

function parseTransactions($transactionHTML)
{
	$html = str_get_html(htmlspecialchars($transactionHTML));
	$rows = $html->find('div.l3-transactions');
	var_dump($rows);


	$stocks = array();
	foreach ($rows as $tr){
		print_r($tr);
		//$desc = $tr->find("td[data-label='description']")->plaintext;
		//echo $desc;
	}
	return $stocks;
}

function stockValues()
{
	$transactionHTML = checkCache();
	if(!$transactionHTML){
		$transactionHTML = refreshCache();
	}
	$stocks = parseTransactions($transactionHTML);
	//print_r($stocks);
}

function checkCache()
{
	global $filename;
	if (file_exists($filename)){
		if (filemtime($filename) > time()-(60*60*24)){
			return file_get_contents($filename);
		}
	}
	return FALSE;
}

function refreshCache()
{
	global $filename;
	login();
	//write file to disk
	$fh = fopen($filename, 'w') or die("can't open file");
	fwrite($fh, getTransactions());
	fclose($fh);
	//delete the cookie file
	//unlink('cookie.txt');
	return checkCache();
}

stockValues();
