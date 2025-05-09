#!/usr/bin/env php
<?php
	require_once(__DIR__ . '/vendor/autoload.php');
	require_once(__DIR__ . '/bind.php');
	require_once(__DIR__ . '/config.php');

	$api = new MyDNSHostAPI($config['api']);

	$api->setAuthUserKey($config['user'], $config['apikey']);
	$api->domainAdmin($config['isAdmin']);
	$api->setRequestTimeout(60);

	$domains = $api->getDomains();
	$errors = [];

	if (!is_array($config['zones'])) { $config['zones'] = [$config['zones']]; }

	foreach ($config['zones'] as $zoneDir) {
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($zoneDir, RecursiveDirectoryIterator::SKIP_DOTS));
		foreach ($it as $file) {
			if (pathinfo($file, PATHINFO_EXTENSION) == "db" || pathinfo($file, PATHINFO_EXTENSION) == "zone") {
				$domain = pathinfo($file, PATHINFO_FILENAME);

				$wantedOwner = $config['newOwner'];
				if (isset($config['newOwnerOverride'][$zoneDir])) { $wantedOwner = $config['newOwnerOverride'][$zoneDir]; }
				if (isset($config['newOwnerOverride'][$domain])) { $wantedOwner = $config['newOwnerOverride'][$domain]; }

				if (!isset($domains[$domain])) {
					echo 'Creating Domain: ', $domain;
					$result = $config['isAdmin'] ? $api->createDomain($domain, $wantedOwner) : $api->createDomain($domain);
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
				} else {
					if ($config['isAdmin']) {
						// Check if domain has $wantedOwner as an owner, if not, abort.
						$wantedOwnerLevel = $domains[$domain]['users'][$wantedOwner] ?? 'none';
						if ($wantedOwnerLevel != 'owner') {
							echo 'Domain exists but ', $wantedOwner, ' is not an owner: ', $domain, ' - Skipping!', "\n";
							continue;
						}
					}
				}

				echo 'Importing Domain: ', $domain;
				$bind = new Bind($domain, '', $file);
				$bind->parseZoneFile();

				$changed = false;

				// Check if NAMESERVERS need changing.
				$domainInfo = $bind->getDomainInfo();
				$oldNS = [];
				if (isset($domainInfo['NS'])) {
					foreach ($domainInfo['NS'][''] as $r) {
						$oldNS[] = $r['Address'];
					}
				}

				if (count(array_diff($oldNS, $config['nameservers'])) > 0 || count(array_diff($config['nameservers'], $oldNS)) > 0) {
					$changed = true;
					$bind->unsetRecord('', 'NS');
					foreach ($config['nameservers'] as $ns) {
						$bind->setRecord('@', 'NS', $ns);
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
	}

	echo 'Done.', "\n";

	if (count($errors) > 0) {
		echo 'There was errors with the following domains: ', "\n";
		print_r($errors);
	}
