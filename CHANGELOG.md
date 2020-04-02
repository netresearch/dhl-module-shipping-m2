# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## 0.11.1 - 2020-04-02

### Fixed

- updated Application ID token

## 0.11.0 - 2019-11-25
### Added
- PHP 7.3 support (for Magento 2.3.3)

### Fixed
- config import and export via CLI, see [Issue #63](https://github.com/netresearch/dhl-module-shipping-m2/issues/63)
- uninstaller missing instruction for database cleanup
- compatibility to Mageplaza OnestepCheckout, see [Issue #60](https://github.com/netresearch/dhl-module-shipping-m2/issues/60)

### Changed
- remove support for Austria (AT) as shipping origin (no new orders will be accepted for DHL, existing ones can still be processed)
- dropped support for Magento 2.1 and PHP 5.6
- reintegrated dhl/module-label-status-m2 as requirement (possible due to Magento 2.1 support drop)
- updated installation instructions in README

## 0.10.3 - 2019-05-13
### Fixed
- Cash On Delivery (COD) - if there is no service selected in checkout
- payment method list lacking options

## 0.10.2 - 2019-04-16
### Fixed
- Cannot select 'Visual Check Of Age' in shipping popup
- Populating select fields in packaging popup
- Persist delivery location details on order place

## 0.10.1 - 2019-03-12
### Fixed
- Web API schema generation
- DHL Paket participation numbers configuration
- Magento 2.1 compatibility

## 0.10.0 - 2019-02-28
### Fixed
- Package weight calculation for mass action and cron autocreate
- Exception when switching store scope (store not found)
- Dhl Shipping configuration now properly hidden if shipping origin country cannot be processed with DHL

### Added
- Support for Magento 2.1 (without inline label status!) and 2.3
- Dhl Wunschpaket services in checkout 
- Disable Cash On Delivery (COD) when 'Preferred Day / Neighbour' is selected
- Service charge configuration, calculation, and display
- German translation

### Changed
- Label status display is now handled by optional module. ONLY installable in Magento 2.2.x and 2.3.x.
- Admin configuration structured into groups

## 0.9.1 - 2018-10-08
### Fixed
- packaging popup not recognizing config settings for weight/length units
- unused config section removed
- wrong version numbers in documentation

## 0.9.0 - 2018-07-24
### Added
- Define custom Export Descriptions via product attribute
- Support for third party shipping methods with dynamic method codes
- Configuration of default products per available route
- Mass action and cron autocreation for cross border shipping
- Dhl Label Status on sales order grid 
- Tarif number validation on packaging popup
- added field for DHL export description
- more clear error message for not supported shipping origin config

### Changed
- Updated DHL product names and codes
- Reordered configuration fields to make dependencies clearer

### Removed
- Removed support for Magento 2.1

### Fixed
- Shipping product options for DE->AT route
- Packaging popup not respecting config defaults for preselects

## 0.8.1 - 2018-05-24
### Fixed
- Payment method selector for COD methods now displays all available methods
- Improved packaging popup template injection to be less invasive

### Security
- Improved output escaping in backend templates

## 0.8.0 - 2018-01-10
### Added
- API support for postal facilities (Packstation, Postfiliale)
- Cancel Business Customer Shipping labels
- Cash On Delivery support for Global Shipping API labels

### Fixed
- Now displaying separate tracking link for Global Shipping API labels
- Reworked product attribute uninstaller
