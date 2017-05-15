<?php
	require_once(__DIR__ . '/bind.php');
	require_once(__DIR__ . '/MyDNSHostAPI.php');
	require_once(__DIR__ . '/requests/library/Requests.php');
	Requests::register_autoloader();
	require_once(__DIR__ . '/config.php');

	$api = new MyDNSHostAPI($config['api']);

	$api->setAuthUserKey($config['user'], $config['apikey']);
	$api->domainAdmin($config['isAdmin']);

	$domains = $api->getDomains();

	$errors = [];

	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($config['zones'], RecursiveDirectoryIterator::SKIP_DOTS));
	foreach ($it as $file) {
		if (pathinfo($file, PATHINFO_EXTENSION) == "db" || pathinfo($file, PATHINFO_EXTENSION) == "zone") {
			$domain = pathinfo($file, PATHINFO_FILENAME);

			if (!isset($domains[$domain])) {
				echo 'Creating Domain: ', $domain;
				$result = $api->createDomain($domain, $config['newOwner']);
				if (isset($result['error'])) {
					$errors[$domain] = 'Unable to create: ' . $result['error'];
					if (isset($result['errorData'])) {
						$errors[$domain] .= ' :: ' . $result['errorData'];
					}
					echo ' - Error!', "\n";
					continue;
				} else {
					echo ' - Success!', "\n";
				}
			}

			echo 'Importing Domain: ', $domain;
			$bind = new Bind($domain, '', $file);
			$bind->parseZoneFile();

			$changed = false;

			// Check if NAMESERVERS need changing.
			$domainInfo = $bind->getDomainInfo();
			$oldNS = [];
			foreach ($domainInfo['NS'][''] as $r) {
				$oldNS[] = $r['Address'];
			}

			if (count(array_diff($oldNS, $config['nameservers'])) > 0 || count(array_diff($config['nameservers'], $oldNS)) > 0) {
				$changed = true;
				$bind->unsetRecord('', 'NS');
				foreach ($config['nameservers'] as $ns) {
					$bind->setRecord('', 'NS', $ns);
				}
			}

			$soa = $bind->getSOA();
			if ($soa['Nameserver'] != $config['nameservers'][0]) {
				$soa['Nameserver'] = $config['nameservers'][0];
				$changed = true;
			}

			if ($changed) {
				$soa['Serial'] = $bind->getNextSerial();
				$bind->setSOA($soa);

				echo ' (Zone has been changed.)';
			}

			$zonedata = implode("\n", $bind->getParsedZoneFile());

			$result = $api->importZone($domain, $zonedata);
			if (isset($result['error'])) {
				$errors[$domain] = 'Unable to import: ' . $result['error'];
				if (isset($result['errorData'])) {
					$errors[$domain] .= ' :: ' . $result['errorData'];
				}
				echo ' - Error!', "\n";
				continue;
			} else {
				echo ' - Success!', "\n";
			}
		}
	}

	echo 'Done.', "\n";

	if (count($errors) > 0) {
		echo 'There was errors with the following domains: ', "\n";
		print_r($errors);
	}
