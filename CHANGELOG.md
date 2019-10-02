# Changelog
All notable changes to this project will be documented in this file.

## [1.0.0] - 2019-04-01
- :dizzy: Initial Release

## [1.1.0] - 2019-04-24
- Added activation functionality
- Replaced deprecated logger code

## [1.2.0] - 2019-04-25
- Added refund functionality

## [1.3.0] - 2019-04-25
- Added cancel functionality

## [1.3.1] - 2019-04-25
- Fixed an issue with the status not getting updated by webhooks

## [1.4.0] - 2019-05-14
- Cancellations webhooks now fire when an order's status is set to 
"cancelled/rejected"
- Activate On Status option added to the plugin config
- Activation webhooks now fire when an order's status corresponds to 
the set _Activate On Status_ config option
- _Limit Plans_ multi-select feature added to the config

## [1.4.1] - 2019-05-22
- The finance payment option is deactivated if the API key is incorrect
- Stricter initial validation of the API Key

## [1.4.2] - 2019-06-28
- Stand suffixes applied to tables
- Take into account constent from merchant when removing data on uninstall
- Add plugin description and change log
- Format and lint PHP code

## [1.4.3] - 2019-07-11
- Config translated into German
- plugin.xml translated into German
- Translations import on installation

## [1.4.4] - 2019-10-01
- Adds German and French translations