<?php

	$config['api'] = 'https://api.mydnshost.co.uk/';

	$config['user'] = 'admin@example.org.uk';
	$config['apikey'] = 'AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE';

	// Local configuration.
	if (file_exists(dirname(__FILE__) . '/config.local.php')) {
		include(dirname(__FILE__) . '/config.local.php');
	}
