<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

function multiRequest($data, $options = array())
{
	$curly = array();
	$result = array();

	$headers = _getHeaders();

	$mh = curl_multi_init();
	foreach ($data as $id => $d)
	{
		$curly[$id] = curl_init();
		$url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
		curl_setopt($curly[$id], CURLOPT_URL,            $url);
		curl_setopt($curly[$id], CURLOPT_HEADER,         0);
		curl_setopt($curly[$id], CURLOPT_HTTPHEADER, 	 $headers);
		curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);
		// post?
		if (is_array($d))
		{
			if (!empty($d['post']))
			{
				$query = http_build_query($d['post']);
				curl_setopt($curly[$id], CURLOPT_POST,       1);
				curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
			}
		}
		curl_setopt($curly[$id], CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curly[$id], CURLOPT_TIMEOUT, 3);
		curl_setopt($curly[$id], CURLOPT_COOKIEFILE, 'uploads/cookie.txt');
		curl_setopt($curly[$id], CURLOPT_COOKIEJAR, 'uploads/cookie.txt');

		// extra options?
		if (!empty($options))
		{
			curl_setopt_array($curly[$id], $options);
		}
		curl_multi_add_handle($mh, $curly[$id]);
	}
	// execute the handles
	$running = null;
	do {
		curl_multi_exec($mh, $running);
	} while($running > 0);
	// get content and remove handles
	foreach($curly as $id => $c)
	{
		$result[$id] = curl_multi_getcontent($c);
		curl_multi_remove_handle($mh, $c);
	}
	// all done
	curl_multi_close($mh);
	return $result;
}

function sendPost($url, $post = array(), $cookie = NULL)
{
	$query = http_build_query($post);
	$headers = _getHeaders($query);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
	curl_setopt($ch, CURLOPT_ENCODING, '');
	curl_setopt($ch, CURLOPT_TIMEOUT, 50);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 50);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.79 Safari/537.36');
	curl_setopt($ch, CURLOPT_COOKIEJAR, 'uploads/cookie.txt');
	curl_setopt($ch, CURLOPT_COOKIEFILE, 'uploads/cookie.txt');
	if($post && !empty($post))
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
	$result = curl_exec($ch);
	
	curl_close($ch);

	return $result;
}

function trackingLogin()
{
	$login = 'Tree';
	$pass = 'TreeDoctors2016';
	$url = 'http://login.genuinetrackingsolutions.com/GTS/geo';

	$dateTimeZone = new DateTimeZone(date_default_timezone_get());
	$dateTime = new DateTime('now', $dateTimeZone);
	$tz = $dateTimeZone->getOffset($dateTime) / 60;

	//var_dump($dateTimeZone, $dateTime, $tz);die;

	$content = sendPost($url, ['op' => 'getPGeoMarker']);

	if(strpos($content, 'name="userId"'))
	{
		$url = 'http://login.genuinetrackingsolutions.com/GTS/login';
		$result = sendPost($url, array('userId' => $login, 'password' => $pass, 'tz' => $tz));//$tz
	}

	return TRUE;
}

function _getHeaders($postQuery = NULL) {
	return 	[
		'Accept:*/*',
		//'Accept-Encoding:gzip, deflate',
		'Accept-Language:ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4,tr;q=0.2,uk;q=0.2',
		'Cache-Control:no-cache',
		'Connection:keep-alive',
		'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
		'Host: login.genuinetrackingsolutions.com',
		/*'Content-Length: ' . (strlen($postQuery) * 2),*/
		'Origin:http://login.genuinetrackingsolutions.com',
		'Pragma:no-cache',
		'Referer:http://login.genuinetrackingsolutions.com/GTS/map.jsp',
		'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.79 Safari/537.36',
		'X-Requested-With:XMLHttpRequest',
	];
}
