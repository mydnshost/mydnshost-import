<?php
	require_once(__DIR__ . '/MyDNSHostAPI.php');
	require_once(__DIR__ . '/config.php');

	$api = new MyDNSHostAPI($config['api']);

	$api->setAuthUserKey($config['user'], $config['apikey']);

	$domains = $api->getDomains();

	var_dump($domains);
