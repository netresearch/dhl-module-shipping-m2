DHL Shipping Extension
======================

The DHL Shipping extension for Magento 2 integrates the DHL business customer
shipping API into the order processing workflow.

Facts
-----
* version: 0.1.0

Description
-----------
This extension enables merchants to request shipping labels for incoming orders
via the DHL business customer shipping API (DHL Geschäftskundenversand-API).

Requirements
------------
* PHP >= 5.6.5

Compatibility
-------------
* Magento >= 2.1.4

Installation Instructions
-------------------------
If you received a ZIP file, extract its contents to the project root directory.
The module sources should then be available in the following directory:

    app
    └── code
        └── Dhl
            └── Shipping

If you prefer to install the module using [composer](https://getcomposer.org/),
run the following commands in your project root directory:

    composer config repositories.dhl-shipping-m2 vcs https://github.com/netresearch/dhl-module-shipping-m2
    composer require dhl/module-shipping-m2:0.1.0

Once the source files are available, make them known to the application:

    ./bin/magento module:enable Dhl_Shipping
    ./bin/magento setup:upgrade

Last but not least, flush cache and compile.

    ./bin/magento cache:flush
    ./bin/magento setup:di:compile

Uninstallation
--------------
To unregister the shipping module from the application, run the following command:

    ./bin/magento module:uninstall Dhl_Shipping

Support
-------
In case of questions or problems, please have a look at the
[Support Portal (FAQ)](http://dhl.support.netresearch.de/) first.

If the issue cannot be resolved, you can contact the support team via the
[Support Portal](http://dhl.support.netresearch.de/) or by sending an email
to <dhl.support@netresearch.de>.

Developer
---------
Christoph Aßmann | [Netresearch GmbH & Co. KG](http://www.netresearch.de/) | [@mam08ixo](https://twitter.com/mam08ixo)

Licence
-------
[OSL - Open Software Licence 3.0](http://opensource.org/licenses/osl-3.0.php)

Copyright
---------
(c) 2017 DHL Paket GmbH
