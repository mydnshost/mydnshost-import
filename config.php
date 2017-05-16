<?php
	// Hostname of API Endpint
	$config['api'] = 'https://api.mydnshost.co.uk/';

	// Username to run as
	$config['user'] = 'admin@example.org';

	// API Key to use to connect
	$config['apikey'] = 'AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE';

	// Directory where zone files exist.
	$config['zones'] = __DIR__ . '/zones';

	// Should we use the admin end points for interacting with domains?
	// (This allows admins to import to other user accounts.)
	$config['isAdmin'] = false;
	// If we are an admin, what user should we create new domains as?
	$config['newOwner'] = null;

	// What nameservers should we change domains to use?
	$config['nameservers'] = [];
	$config['nameservers'][] = 'ns1.mydnshost.co.uk.';
	$config['nameservers'][] = 'ns2.mydnshost.co.uk.';
	$config['nameservers'][] = 'ns3.mydnshost.co.uk.';
	$config['nameservers'][] = 'ns4.mydnshost.co.uk.';

	// Local configuration.
	if (file_exists(dirname(__FILE__) . '/config.local.php')) {
		include(dirname(__FILE__) . '/config.local.php');
	}
