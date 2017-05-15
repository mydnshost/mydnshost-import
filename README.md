# MyDNSHost bulk importer

This repository contains code to take a directory of zone files and bulk-import their contents into http://www.mydnshost.co.uk/

## Usage:

- Clone the repo
  - Update submodules with `git submodule update --init`
- Create config.local.php with your user and API key:
```
	$config['user'] = 'admin@example.org.uk';
	$config['apikey'] = 'AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE';
```
- Add zone files (ending in `.zone` or `.db`) into the `zones/` directory
- Run the importer with `php run.php`
