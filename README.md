**Warning: This extension is no longer supported and should not be used. You can find the official replacement extension [here on GitHub](https://github.com/netresearch/dhl-shipping-m2)**

DHL Shipping Extension
======================

The DHL Shipping extension for Magento® 2 integrates the DHL Business Customer
Shipping API or the DHL eCommerce Global Label API into the order processing workflow.

Description
-----------

This extension enables merchants to request shipping labels for incoming orders
via the [DHL Business Customer Shipping API](https://entwickler.dhl.de/en/)
(DHL Geschäftskundenversand-API) or the DHL eCommerce Global Label API.

For more details on the API connections, see the [documentation](http://dhl.support.netresearch.de/support/solutions/articles/12000023174).

Requirements
------------

* PHP >= 7.0.6
* PHP >= 7.1.0
* PHP >= 7.2.0
* PHP >= 7.3.0

Compatibility
-------------

* Magento >= 2.2.4
* Magento >= 2.3.0

Installation Instructions
-------------------------

### Install Source Files ###

The DHL Shipping module for Magento® 2 can be installed from the following sources:
* [Composer Repository](https://getcomposer.org/doc/05-repositories.md#composer)
* [VCS Repository](https://getcomposer.org/doc/05-repositories.md#using-private-repositories)

#### Integrators ####

As an integrator you installed Magento using Composer and acquired the [DHL Shipping
module on Magento Marketplace](https://marketplace.magento.com/dhl-module-shipping-m2.html)
(free of charge).

The Composer repository https://repo.magento.com/ is declared in your root `composer.json`
which allows you to directly install the module like this:

    composer require dhl/module-shipping-m2

During installation, Composer might ask for a user and password. You must use the public and
private key of the Magento Marketplace user which was used to purchase the module.

#### Developers ####

If you want to contribute to the module, you can declare the GitHub repository in your
root `composer.json` and install the module like this:

    composer config repositories.dhl-shipping-m2 vcs https://github.com/netresearch/dhl-module-shipping-m2.git
    composer require dhl/module-shipping-m2

### Enable Module ###

Once the source files are installed, make them known to the application:

    ./bin/magento module:enable Dhl_Shipping
    ./bin/magento setup:upgrade

And finally: flush the cache, compile, and deploy the static content:

    ./bin/magento cache:flush
    ./bin/magento setup:di:compile
    ./bin/magento setup:static-content:deploy <list_of_locales>

The list of locales could be something like: en_US en_GB fr_FR de_DE it_IT

Uninstallation
--------------

The following sections describe how to uninstall the module from your Magento® 2 instance. 

#### Composer ####

To unregister the shipping module from the application, run the following command:

    ./bin/magento module:uninstall --remove-data Dhl_Shipping
    composer update
    
This will automatically remove source files, clean up the database, update package dependencies.

#### Manual Steps ####

To uninstall the module manually, run the following commands in your project
root directory:

    ./bin/magento module:disable Dhl_Shipping
    composer remove dhl/module-shipping-m2

To clean up the database, run the following commands:

    DROP TABLE `dhlshipping_quote_address`, `dhlshipping_order_address`;
    DROP TABLE `dhlshipping_quote_address_service_selection`, `dhlshipping_order_address_service_selection`;

    DELETE FROM `eav_attribute` WHERE `attribute_code` IN ('dhl_dangerous_goods_category', 'dhl_tariff_number', 'dhl_export_description');

    ALTER TABLE `quote` DROP COLUMN `dhl_service_charge`, DROP COLUMN `base_dhl_service_charge`;
    ALTER TABLE `quote_address` DROP COLUMN `dhl_service_charge`, DROP COLUMN `base_dhl_service_charge`;
    ALTER TABLE `sales_order` DROP COLUMN `dhl_service_charge`, DROP COLUMN `base_dhl_service_charge`;
    ALTER TABLE `sales_invoice` DROP COLUMN `dhl_service_charge`, DROP COLUMN `base_dhl_service_charge`;
    ALTER TABLE `sales_creditmemo` DROP COLUMN `dhl_service_charge`, DROP COLUMN `base_dhl_service_charge`;
    DELETE FROM `core_config_data` WHERE `path` LIKE 'carriers/dhlshipping/%';
    DELETE FROM `setup_module` WHERE `module` = 'Dhl_Shipping';

Support
-------

In case of questions or problems, please have a look at the
[Support Portal (FAQ)](http://dhl.support.netresearch.de/) first.

Also check the [user documentation](http://dhl.support.netresearch.de/support/solutions/articles/12000023174).

If the issue cannot be resolved, you can contact the support team via the
[Support Portal](http://dhl.support.netresearch.de/) or by sending an email
to <dhl.support@netresearch.de>.

Developer
---------

* Christoph Aßmann | [Netresearch GmbH & Co. KG](http://www.netresearch.de/) | [@mam08ixo](https://twitter.com/mam08ixo)
* Sebastian Ertner | [Netresearch GmbH & Co. KG](http://www.netresearch.de/)
* Benjamin Heuer | [Netresearch GmbH & Co. KG](http://www.netresearch.de/)
* Paul Siedler | [Netresearch GmbH & Co. KG](http://www.netresearch.de/) | [@powlomat](https://twitter.com/powlomat)

License
-------

[OSL - Open Software Licence 3.0](http://opensource.org/licenses/osl-3.0.php)

Copyright
---------

(c) 2019 DHL Paket GmbH
