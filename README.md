# Amazd Shopware integration

## Features

- Integration of Amazd wishbag-to-checkout flow


## Requirements

| Version 	| Requirements               	|
|---------	|----------------------------	|
| 1.0.0    	| Min. Shopware 6.4 	        |


This project uses the [MIT License](LICENCE.md).

## Installation

- Download latest version https://github.com/amazd-code/shopware-integration/releases (ShopwareAmazdIntegration.zip)
- Open Shopware admin -> Extensions -> My extensions -> Upload extension -> choose your zip. Install and activate the plugin.


## Development.

1. Run Shopware locally: `docker run -p 80:80 dockware/dev:latest`
2. Zip this directory. Make sure your zip archive contains directory with the same name as plugin. `ShopwareAmazdIntegration.zip` -> `ShopwareAmazdIntegration` -> `{files of this repo}`.
3. Open Shopware admin -> Extensions -> My extensions -> Upload extension -> choose your zip. Install and activate the plugin.
4. Run `watch.js` script to watch files and change them in the container. `node dev/watch.js {containerId}`.