<?php

	$config['api'] = 'https://api.mydnshost.co.uk/';

	$config['user'] = 'admin@example.org.uk';
	$config['apikey'] = 'AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE';

	$config['zones'] = __DIR__ . '/zones';

	$config['isAdmin'] = false;
	$config['newOwner'] = null;

	$config['nameservers'] = [];
	$config['nameservers'][] = 'ns1.mydnshost.co.uk.';
	$config['nameservers'][] = 'ns2.mydnshost.co.uk.';
	$config['nameservers'][] = 'ns3.mydnshost.co.uk.';
	$config['nameservers'][] = 'ns4.mydnshost.co.uk.';

	// Local configuration.
	if (file_exists(dirname(__FILE__) . '/config.local.php')) {
		include(dirname(__FILE__) . '/config.local.php');
	}
