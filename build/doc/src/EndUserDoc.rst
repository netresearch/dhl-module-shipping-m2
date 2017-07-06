.. |date| date:: %d/%m/%Y
.. |year| date:: %Y

.. footer::
   .. class:: footertable

   +-------------------------+-------------------------+
   | Stand: |date|           | .. class:: rightalign   |
   |                         |                         |
   |                         | ###Page###/###Total###  |
   +-------------------------+-------------------------+

.. header::
   .. image:: images/dhl.jpg
      :width: 4.5cm
      :height: 1.2cm
      :align: right

.. sectnum::

======================================================
DHL Shipping (Versenden) M2 for DHL Business Customers
======================================================

The module *DHL Shipping (Versenden)* for Magento® 2 enables merchants with a
DHL account to create shipments and retrieve shipping labels.

The webservices *DHL Business Customer Shipping (Geschäftskundenversand) API*
and *eCommerce Global Label API* are supported. Which of these webservices
can be used depends on the shipping origin.

.. raw:: pdf

   PageBreak

.. contents:: End user documentation

.. raw:: pdf

   PageBreak


Requirements
============

The following requirements must be met for a smooth operation of the module:

Magento® 2
----------

The following Magento® 2 versions are supported:

- Community Edition 2.1.4 or higher

PHP
---

These PHP versions are supported:

- PHP 5.6.5+
- PHP 7.0.6+

Further information can also be found in the files *README.md* and *composer.json* in
the module package. If in doubt: the version information in the file *composer.json*
supersedes any other information.

See also https://github.com/netresearch/dhl-module-shipping-m2/tree/0.2.0

To connect to the API (webservice), the PHP SOAP extension must be installed 
and enabled on the web server.


Hints for using the module
==========================

Shipping origin and currency
----------------------------

When using the *DHL Business Customer Shipping (Geschäftskundenversand) API* shipments
must originate from Germany or Austria. The sender address of the shop must be located
in one of those countries.

When using the *eCommerce Global Label API* shipments can be sent from / to the following
countries: USA, China (incl. Hong Kong), Singapore, Thailand, Malaysia. Please also note
the information in section `International shipments`_ further down.

In any case, make sure that the sender address information in the configuration sections
mentioned in `Module configuration`_ is correct.

The base currency is assumed to be the official currency of the sender country which is
set in the Magento configuration. There is no automated conversion between currencies.



Installation and configuration
==============================

This section explains how to install and configure the module.

Installation
------------

Install the module according to the instructions from the file *README.md* which you can
find in the module package. It is very important to follow all steps exactly as shown there.
Do not skip any steps.

The file *README.md* also describes the database changes which are made during installation.

The *README.md* is linked in the section `Requirements`_.

Module configuration
--------------------

There are three configuration sections which are relevant for creating shipments:

::

    Stores → Configuration → General → General → Store-Information
    Stores → Configuration → Sales → Shipping Settings → Origin
    Stores → Configuration → Sales → Shipping Methods → DHL Versenden (Shipping)

Make sure that the following required fields in the sections *Store Information* 
and *Origin* are filled in completely:

* Store Information

  * Store Name
  * Store Contact Telephone
* Origin

  * Country
  * Region / State
  * ZIP / Postal Code
  * City
  * Street Address

If you are shipping from multiple countries, you can configure different sender addresses
on the Store or StoreView level.

.. admonition:: Note

   The section *Shipping Methods → DHL* is a core part of Magento® 2 which connects
   to the webservice of DHL USA only. These settings are not relevant for the *DHL Shipping
   (Versenden)* module.

.. raw:: pdf

   PageBreak

General Settings
~~~~~~~~~~~~~~~~

In the configuration section *General Settings* you configure which API connection should
be used. This setting depends on your DHL account / contract. Choose between:

* DHL Business Customer Shipping (Geschäftskundenversand), or
* DHL eCommerce Global Label API

You can choose if you want to run the module in *Sandbox Mode* to test the integration,
or using the production mode.

If the logging is enabled in the DHL module, the webservice messages will be recorded
in the log files in ``var/log``. There will be *no separate* log file for the DHL module.

You can choose between three log levels:

* ``Error`` records communication errors between the shop and the DHL webservice.
* ``Warning`` records communication errors and also errors related to the message 
  content (e.g. address validation failed, invalid services selected).
* ``Debug`` records all messages, including downloaded labels.

.. admonition:: Note

   Make sure to clear or rotate the log files regularly. The log level *Debug* should
   only be set while resolving problems, because it can result in very large log files.

Configuration options that are not described here are not relevant.

Account Data
~~~~~~~~~~~~

The next configuration section holds your access credentials for the DHL webservice 
which are required for production mode. You will get this information directly from
DHL.

The input fields are only visible if the Sandbox Mode is disabled.

When using *DHL Business Customer Shipping (Geschäftskundenversand)* enter the
following data:

* Username (German: Benutzername)
* Signature (German: Passwort)
* EKP (DHL account number, 10 digits)
* Participation numbers (German: Teilnahmenummern, two digits per number)

When using the *eCommerce Global Label API* you don't need the above data. Enter the
following data instead which you received from DHL:

* Pickup Account Number (5 to 10 digits)
* Distribution Center (6 digits)
* Client ID
* Client Secret

.. raw:: pdf

   PageBreak

Shipment Orders
~~~~~~~~~~~~~~~

In the section *Shipment Orders*, the configuration for creating shipments via 
the DHL webservice is made.

* *Print only if codeable*: If this is enabled, only shipments with perfectly 
  valid addresses will be accepted by DHL. Otherwise, DHL will reject the shipment 
  and issue an error message. If this option is disabled, DHL will attempt to 
  correct an invalid address automatically, which results in an additional charge 
  (Nachcodierungsentgelt). If the address cannot be corrected, DHL will still 
  reject the shipment.
* *Shipping Methods for DHL Versenden*: Select which shipping methods should be
  used for calculating shipping costs in the checkout. Only shipping methods that are
  selected here will be handled by the DHL extension when creating shipments.
* *Default product*: Set the DHL product which should be used by default for creating
  shipments. Please note the information in section `Module configuration`_ regarding
  the sender (origin) address.
* *Cash On Delivery payment methods for DHL Versenden*: Select which payment methods
  should be treated as Cash On Delivery (COD) payment methods. This is necessary 
  to transmit the additional charge for Cash On Delivery to the DHL webservice 
  and create Cash On Delivery labels. This service is only availabe when using the
  *DHL Business Customer Shipping (Geschäftskundenversand)*.

Contact Data
~~~~~~~~~~~~

In the section *Contact Data* you configure which additional sender information
should be transmitted to DHL. The sender information from the general Magento
configuration will also be used.

When using the *eCommerce Global Label API* no additional information can be entered
here.

Bank Data
~~~~~~~~~

In the section *Bank Data* you configure the bank account to be used for Cash On 
Delivery (COD) shipments with DHL. The Cash On Delivery amount from the customer 
will be transferred to this bank account.

This section is not visible when using the *eCommerce Global Label API* because it
does not allow Cash On Delivery shipments.


Workflow and features
=====================

Creating an order
-----------------

The following section describes how the DHL extension integrates itself into the order 
process.

Checkout
~~~~~~~~

In the `Module configuration`_ the shipping methods have been selected for which DHL 
shipments and labels should be created. If the customer now selects one of those 
shipping methods in the checkout, the shipment can later be processed by DHL.

In the checkout step *Payment information* the Cash On Delivery payment methods 
will be disabled if Cash On Delivery is not available for the selected delivery 
address (see *Cash On Delivery payment methods for DHL Versenden*).

Admin Order
~~~~~~~~~~~

When creating orders via the Admin Panel, the Cash On Delivery payment methods
will be disabled if Cash On Delivery is not  available for the delivery address
(same behaviour as in the checkout).

Creating a shipment
-------------------

The following section explains how to create a shipment for an order and how 
to retrieve the shipping label.

National shipments
~~~~~~~~~~~~~~~~~~

In the Admin Panel, select an order whose shipping method is linked to DHL (see 
`Module configuration`_, section *Shipping Methods for DHL Versenden*). Then 
click the button *Ship* on the top of the page.

.. image:: images/en/button_ship.png
   :scale: 75 %

You will get to the page *New shipment for order*. Activate the checkbox 
*Create shipping label* and click the button *Submit shipment...*.

.. image:: images/en/button_submit_shipment.png
   :scale: 75 %

Now a popup window for selecting the articles in the package will be opened. The
default product from the section `Shipment Orders`_ will be pre-selected. Click 
the button *Add products*, select the products, and confirm by clicking 
*Add selected product(s) to package*. The package dimensions are optional.

.. admonition:: Note

   Splitting the products / items into multiple packages is currently not supported 
   by the DHL webservice. As an alternative, you can create several Magento® shipments
   for one order (partial shipment) For each shipment you can then create a separate
   DHL label.

The button *OK* in the popup window is now enabled. When clicking it, the shipment 
will be transmitted to DHL and (if the transmission was successful) a shipping 
label will be retrieved.

If there was an error, the message from the DHL webservice will be displayed at the top
of the popup, and you can correct the data accordingly, see also `Troubleshooting`_. You
might have to scroll up in the popup to see the error message.

.. raw:: pdf

   PageBreak

International shipments
~~~~~~~~~~~~~~~~~~~~~~~

When using *DHL Business Customer Shipping (Geschäftskundenversand)* only shipments
within the EU can be processed, because the extension cannot create the export documents
(customs declaration). This feature will be implemented in a later module version.

When using the *eCommerce Global Label API* you can only ship within the origin country
(e.g. from China to China, but not from China to the USA). Also note the information
regarding the allowed countries in the section `Shipping origin and currency`_ further up.

Everything else is the same as described in the section `National shipments`_.

Printing a shipping label
-------------------------

The successfully retrieved shipping labels can be opened in several locations 
of the Admin Panel:

* Sales → Orders → Mass action *Print shipping labels*
* Sales → Shipments → Mass action *Print shipping labels*
* Detail page of a shipment → Button *Print shipping label*

.. admonition:: Note

   If you are using a German locale, the exact names of the German menu entries
   *Bestellungen* or *Lieferscheine* can differ slightly, depending on the installed
   Language Pack (e.g. *Aufträge* or *Lieferungen*). However, this is not important
   for the usage.

.. raw:: pdf

   PageBreak

Canceling a shipment
--------------------

As long as a shipment has not been manifested, it can be canceled at DHL.

However, currently the shipment cannot be canceled at DHL by clicking the link *Delete*
in the box *Shipping and tracking information* next to the tracking number. This only
deletes the tracking number in Magento.

.. image:: images/en/shipping_and_tracking.png
   :scale: 75 %

To cancel the shipment, please use the usual way via the DHL website (depending on the
API connection you are using, e.g. the DHL Business Customer Portal). This feature will
be implemented for Business Customer Shipping into the DHL module at a later time.

.. admonition:: Note

   If you only delete the tracking number in Magento without cancelling the shipment
   at DHL, you will be charged by DHL for the shipping cost.

.. raw:: pdf

   PageBreak

Troubleshooting
---------------

During the transmission of shipments to DHL, errors can occur. These are often 
caused by an invalid address or an invalid combination of additional services.

When creating shipments manually, the error message will be directly visible in
the popup. You might have to scroll up inside the popup to see the message.

If the logging is enabled in the `Module Configuration`_, you can also check the
shipments in the log files.

Erroneous shipment requests can be corrected as follows:

* In the popup window for selecting the package articles, you can correct invalid
  information.
* On the detail page of the order or shipment, you can edit the receiver address 
  and correct any errors. Use the link *Edit* in the box *Shipping address*.

  .. image:: images/en/edit_address_link.png
     :scale: 75 %

  On this page, you can edit the address fields in the upper part, and the special 
  fields for DHL shipping in the lower part:

  * Street name (without house number)
  * House number (separately)
  * Address addition

.. image:: images/en/edit_address_form.png
   :scale: 75 %

Afterwards, save the address. If the error has been corrected, you can retry 
`Creating a shipment`_.

If a shipment has already been transmitted successfully via the webservice, but 
you want to make changes afterwards, please cancel the shipment first as described 
in the section `Canceling a shipment`_. Then click *Create shipping label...* 
inside the same box *Shipping and tracking information*. From here on, the 
process is the same as described in `Creating a shipment`_.

.. raw:: pdf

   PageBreak

Uninstalling the module
=======================

To uninstall the module, follow these steps described in the file *README.md* from
the module package.

The *README.md* is linked in the section `Requirements`_.


Technical support
=================

In case of questions or problems, please have a look at the Support Portal 
(FAQ) first: http://dhl.support.netresearch.de/

If the problem cannot be resolved, you can contact the support team via the 
Support Portal or by sending an email to dhl.support@netresearch.de
