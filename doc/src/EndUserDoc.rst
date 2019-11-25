.. |date| date:: %Y-%m-%d
.. |year| date:: %Y
.. |mage| unicode:: Magento U+00AE
.. |mage2| replace:: |mage| 2

.. footer::
   .. class:: footertable

   +-------------------------+-------------------------+
   | Last updated: |date|    | .. class:: rightalign   |
   |                         |                         |
   |                         | ###Page###/###Total###  |
   +-------------------------+-------------------------+

.. header::
   .. image:: images/dhl.jpg
      :width: 4.5cm
      :height: 1.2cm
      :align: right

.. sectnum::

========================
DHL Shipping for |mage2|
========================

The module *DHL Shipping* for |mage2| enables merchants with a DHL account to
create shipments and retrieve shipping labels.

The module supports the following webservices:

* DHL Paket Business Customer Shipping (Geschäftskundenversand) API
* DHL eCommerce Global Shipping API

Which of these webservices is actually used depends on the shipping origin country.

.. raw:: pdf

   PageBreak

.. contents:: End user documentation

.. raw:: pdf

   PageBreak


Requirements
============

The following requirements must be met for a smooth operation of the module.

|mage2|
-------

The following |mage2| versions are supported:

- Community Edition 2.2.4+
- Community Edition 2.3.0+

PHP
---

These PHP versions are supported:

- PHP 7.0.2
- PHP 7.0.4
- PHP 7.0.6+
- PHP 7.1.0+
- PHP 7.2.0+
- PHP 7.3.0+

To connect to the API (webservice), the PHP SOAP extension must be installed
and enabled on the web server.

Further information can also be found in these files inside the module package / repository:

* README.md
* composer.json

If in doubt: the version information in the file *composer.json* supersedes any
other information.

.. admonition:: Repository

   The public Git repository can be found here:
   
   https://github.com/netresearch/dhl-module-shipping-m2/

   README.md with installation instructions:

   https://github.com/netresearch/dhl-module-shipping-m2/blob/master/README.md


Hints for using the module
==========================

Shipping origin
---------------

The DHL webservices (APIs) only support the following origin countries:

**DHL Business Customer Shipping (Geschäftskundenversand) API**

* Germany

.. CAUTION::
   Austria(AT) is no longer supported

**eCommerce Global Label API**

* Australia
* Canada
* Chile
* China
* Hongkong
* India
* Japan
* Malaysia
* New Zealand
* Singapore
* Thailand
* USA
* Vietnam

The shop's shipping origin address must be located in one of the above countries, and it
must be entered completely into the `Module configuration`_.

Please also note the information in section `International shipments`_.

Currency
--------

The base currency is assumed to be the official currency of the sender country which is
set in the |mage| configuration. There is no automated conversion between currencies.

Data protection
---------------

The module transmits personal data to DHL which are needed to process the shipment (names,
addresses, phone numbers, email addresses, etc.). The amount of data depends on the
`Module configuration`_ as well as the booked `Additional Services In Checkout`_.

The merchant must obtain consent from the customer to process the data, e.g. via the shop's
terms and conditions and / or an agreement in the checkout (|mage2| Checkout Agreements).

.. raw:: pdf

   PageBreak

Installation and configuration
==============================

Installation
------------

Install the module according to the instructions from the file *README.md* (see section `Requirements`_).

We recommend installing the module with Composer. It is very important to follow all steps exactly.
Do not skip any steps.

Any database changes during installation are also shown in the file *README.md*.

.. admonition:: Additional module for DHL label status required

   Since **version 0.10.0** you need to install the additional module
   `dhl/module-label-status <https://github.com/netresearch/dhl-module-label-status>`_ to see the
   `Shipment Overview`_. During installation with Composer, this additional module will be suggested,
   but it is not installed by default.

   The additional module can only be installed in |mage| 2.2.x or 2.3.x. |mage| **2.1.x is not supported**.
   The DHL label status will not be shown in the order list.

Module configuration
--------------------

There are three configuration sections which are relevant for creating shipments:

::

    Stores → Configuration → General → General → Store-Information
    Stores → Configuration → Sales → Shipping Settings → Origin
    Stores → Configuration → Sales → Shipping Methods → DHL Shipping

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

If you are shipping from multiple countries, you can configure different sender
addresses on *Website* or *Store* level.

.. admonition:: Note

   The section *Shipping Methods → DHL* is a core part of |mage2| which connects
   to the webservice of DHL USA only. These settings are not relevant for the *DHL Shipping* module.

General Settings
~~~~~~~~~~~~~~~~

The dropdown in the configuration section *General Settings* shows which
API connection is being configured.

* DHL Business Customer Shipping (DE), or
* DHL eCommerce Global Label API

This field is pre-selected according to the current `Shipping origin`_. Depending on the
selection, different configuration fields are shown below.

.. admonition:: Note about the API

   The actual API connection to be used depends on the `Shipping origin`_
   and is selected automatically during transmission to DHL. The aforementioned dropdown
   only makes the configuration fields visible. It does not select which API will actually
   be used.

You can choose if you want to run the module in *Sandbox Mode* to test the integration,
or use the *production mode*.

If the logging is enabled in the DHL module, the webservice messages will be recorded
in the log file ``var/log/debug.log``. There will be *no separate* log file for the DHL module.
Also note these `hints about logging <http://dhl.support.netresearch.de/support/solutions/articles/12000051181>`_.

You can choose between three log levels:

- *Error:* Records communication errors between the shop and the DHL webservice.
- *Warning:* Records communication errors and also errors due to invalid shipment
  data (e.g. address validation failed, invalid services selected).
- *Debug:* Record all messages, including downloaded label raw data in the log.

Make sure to archive or rotate the log files regularly. The log level *Debug* should
only be set while resolving problems, because it will result in very large log files
over time.

.. raw:: pdf

   PageBreak

Account Data
~~~~~~~~~~~~

This configuration section holds your access credentials for the DHL webservice
which are required for production mode. You will get this information directly from
DHL.

When using *DHL Business Customer Shipping (Geschäftskundenversand)* in sandbox
mode, no additional input is necessary.

When using *DHL Business Customer Shipping (Geschäftskundenversand)* in production,
enter the following data:

* Username (German: Benutzername)
* Signature (German: Passwort)
* EKP (DHL account number, 10 digits)
* Participation numbers (German: Teilnahmenummern, two digits per field)

.. admonition:: Configuration of billing numbers

  A detailled tutorial for configuring the billing numbers, DHL products, and participation numbers can
  be found in this `article in the Knowledge Base <http://dhl.support.netresearch.de/support/solutions/articles/12000024659>`_.

When using the *eCommerce Global Label API*, enter the following data:

* Pickup Account Number (5 to 10 digits)
* Customer Prefix (up to 5 digits)
* Distribution Center (6 digits)
* Client ID
* Client Secret

General Shipping Settings
~~~~~~~~~~~~~~~~~~~~~~~~~

* *Shipping Methods for DHL Shipping*: Select which shipping methods should be
  used for calculating shipping costs in the checkout. Only shipping methods that are
  selected here will be handled by the DHL extension when creating shipments.

.. raw:: pdf

   PageBreak

Additional Services In Checkout
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In the configuration section *Additional Services In Checkout* you can choose which
additional DHL services you want to offer to your customers.

Please also note the information about `Booking additional services`_ and
`Additional costs for services`_.

* *Enable Preferred Location*: The customer can state an alternative location where
  the shipment can be placed in case they are not at home.
* *Enable Preferred Neighbor*: The customer can state an alternative address in the
  neighborhood for the shipment in case they are not at home.
* *Enable Parcel Announcement*: The customer can choose to be notified via email about the status
  of the shipment. The customer's email address will be transmitted to DHL for this service
  (note the section `Data protection`_). Select one of the following options:

  * *Yes*:The customer decides in the checkout if the service should be booked.
  * *No*: No option is shown in the checkout. The service will not be booked.

* *Enable Preferred Day*: The customer can choose a specific day on which the shipment
  should arrive. The available days are displayed dynamically, depending on the recipient's
  address and your configured drop-off days.
* *Enable Preferred Time*: The customer can choose a time frame within which the
  shipment should arrive. The available times are displayed dynamically, depending on the recipient's
  address.
* *Service charge for Preferred day / time*: This amount will
  be added to the shipping cost if the corresponding service is used. Use a decimal point, not comma.
  The gross amount must be entered here (incl. VAT). If you want to offer the service
  for free, enter ``0``.
* *Preferred day / time handling fee text*: This text will be displayed to the customer
  in the checkout to explain the handling fee. You can use the placeholder ``$1``
  in the text which will be substituted with configured handling fee and currency in the checkout.
* *Cut-off time*: This sets the time up to which new orders will be dispatched by you on the
  same day. Orders placed *after* the cut-off time will not be dispatched by you on the same
  day. This affects the Preferred Days available to customers
* *Days excluded from drop-off*: Select the days on which you do *not* hand over shipments to
  DHL. This affects the Preferred Days available to customers.
* *Service charge for preferred day and time combined*: This amount will
  be added to the shipping cost if *both* services are booked. Use a decimal point, not comma.
  The gross amount must be entered here (incl. VAT). If you want to offer the services combination
  for free, enter ``0``.
* *Combined service charge text*: This text will be displayed to the customer
  in the checkout to explain the combined handling fee. You can use the placeholder ``$1``
  in the text which will show the additional handling fee and currency in the checkout.

.. raw:: pdf

   PageBreak

Cash On Delivery Settings
~~~~~~~~~~~~~~~~~~~~~~~~~

- *Cash On Delivery payment methods*: Select which payment methods
  should be treated as Cash On Delivery (COD) payment methods. Based on this, the COD charge will be
  transmitted to the DHL webservice and Cash On Delivery labels are created. If COD is not available,
  these payment methods will be hidden in the checkout.

- Configure the bank account to be used for Cash On Delivery (COD) shipments with DHL. The Cash On Delivery
  amount from the customer will be transferred to this bank account by DHL.

  Please note that you might also have to store the bank data in your DHL account.
  Usually, this can be done through the DHL Business Customer Portal (Geschäftskundenportal).

When using the *eCommerce Global Label API*, the service Cash On Delivery is not available.

Default shipping label creation settings
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In this section you can configure the default settings for shipments.

Depending on the selected API (DHL Business Customer Shipping or eCommerce Global Label API)
different options are displayed.

* *Default product*: Shows the DHL product which will be used by default for creating
  shipments. The available products are choosen automatically depending on the configured shipping origin.
  Please note the information in section `Module configuration`_ regarding
  the sender (origin) address.
* *Default Terms of Trade*: Select the default terms of trade for customs handling.
* *Default Place of Commital*: Select the default place of commitial for customs handling.
* *Default Additional Fee*: Additional fee for customs handling.
* *Default Export Content Type*: Content type of the shipment for customs handling.

The customs information can also be set via `Additional Product Attributes`_, see also the
section `International shipments`_.

.. raw:: pdf

   PageBreak

Additional Shipping Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

These settings apply only to bulk shipments (mass action) and shipments automatically created via Cronjob.

* *Use Print only if codeable service*: If this is enabled, only shipments with 100 %
  valid addresses will be accepted by DHL. Otherwise, DHL will reject the shipment
  and issue an error message. If this option is disabled, DHL will attempt to
  correct an invalid address automatically, which results in an additional charge
  (Nachkodierungsentgelt). If the address cannot be corrected, DHL will still
  reject the shipment.

* *Use Visual Check of Age service:* Select if the service for age verification should be
  booked, and what the minimum age is. Options:

  * *No*: The service will not be booked.
  * *A16:* Minimum age 16 years.
  * *A18:* Minimum age 18 years.

* *Use Return Shipment service:* Select if a return label should be created together with the
  shipping label. See also `Printing a return slip`_.
* *Use Additional Insurance service:* Select if an additional insurance should be booked for
  the shipment.
* *Use Bulky Goods service:* Select if the service for bulky goods (bulk freight) should be booked.

eCommerce Global API Shipping Settings
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In this section you can configure the label size, page size, and layout.

Automatic Shipping Label Creation
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The section *Automatic Shipment Creation* lets you choose if shipments should be
created and package labels retrieved automatically.

You can also configure which order status an order must have to be processed
automatically. You can use this to exclude specific orders from being processed
automatically.

Also, you can choose whether or not an email will be be sent to the customer when the
shipment has been created. This refers to the |mage| shipment confirmation email,
not the parcel announcement from DHL.

.. admonition:: Note

   Automated shipment creation requires working |mage2| Cronjobs.

.. raw:: pdf

   PageBreak

Additional Product Attributes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The module introduces the new product attributes **DHL Export Description** and
**Tariff number** which can be used for international shipments.

These atrributes allow storing the customs information in the system, so the data
doesn't have to be entered manually for every shipment.

Please note the maximum length of:

 * 50 characters for DHL Export Description
 * 10 characters for Tariff Number

Also note the section `International shipments`_.

Booking additional services
---------------------------

The available services as well as preferred days and preferred times depend on
the shipping address and country of the customer. The DHL Parcel Management API
is used for this during the checkout process. Unusable services will be hidden from
the checkout automatically.

If the order contains articles which are not in stock, it will not be possible to book
Preferred Day.

The services *Preferred location* and *Preferred neighbor* can not be booked together.

Additional costs for services
-----------------------------

The services *Preferred Day* and *Preferred Time* are **enabled by default!**
Therefore the standard DHL handling fees will be added to your shipping cost every time
a customer selects one of these services.

When using the shipping method *Free Shipping*, the additional handling fees will
always be ignored!

If you want to use the shipping method *Table Rates* and set a threshold for free
shipping, we recommend setting up a Shopping Cart Price Rule for this. By using this
shipping method the additional fees for DHL services will be included.

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
address (see *Cash On Delivery payment methods for DHL Shipping*).

Admin Order
~~~~~~~~~~~

When creating orders via the Admin Panel, the Cash On Delivery payment methods
will be disabled if Cash On Delivery is not available for the delivery address
(same behaviour as in the checkout).

DHL Delivery Addresses (Packing Stations, Post Offices)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
The module offers limited support for DHL delivery addresses in the checkout:

* The format *Packstation 123* in the field *Street* will be recognized.
* The format *Postfiliale 123* in the field *Street* will be recognized.
* A numerical value in the field *Company* will be recognized as Post Number.

.. admonition:: Note

   For successful transmission to DHL, the above information must be entered in
   the correct format.

   See also `Shipping to post offices <https://www.dhl.de/en/privatkunden/pakete-empfangen/an-einem-abholort-empfangen/filiale-empfang.html>`_
   and `Shipping to Packstations <https://www.dhl.de/en/privatkunden/pakete-empfangen/an-einem-abholort-empfangen/packstation-empfang.html>`_.

.. raw:: pdf

   PageBreak

Creating a shipment
-------------------

The following section explains how to create a shipment for an order and how
to retrieve the shipping label.

National shipments
~~~~~~~~~~~~~~~~~~

In the Admin Panel, select an order with a shipping method linked to DHL (see
`Module configuration`_, section *Shipping Methods for DHL Shipping*).

Then click the button *Ship* on the top of the page.

.. image:: images/en/button_ship.png
   :scale: 75 %

You will get to the page *New shipment for order*.

Activate the checkbox *Create shipping label* and click the button *Submit Shipment...*.

.. image:: images/en/button_submit_shipment.png
   :scale: 75 %

Now a popup window for selecting the shipping items in the package will be opened. The
default product from the section `General Shipping Settings`_ will be pre-selected.

Click the button *Add products*, select *all* products, and confirm by clicking
*Add selected product(s) to package*.

The package dimensions are optional. Make sure the weight is correct.

The button *OK* in the popup window is now enabled. When clicking it, the shipment
will be transmitted to DHL and (if the transmission was successful) a shipping
label will be retrieved.

If there was an error, the message from the DHL webservice will be displayed at the top
of the popup. You might have to scroll up inside the popup to see the error message.

The incorrect data can now be corrected, see also `Troubleshooting`_.

.. raw:: pdf

   PageBreak

International shipments
~~~~~~~~~~~~~~~~~~~~~~~

For international shipments, information for the customs declaration might be needed.

In particular:

*  When using *DHL Business Customer Shipping (Geschäftskundenversand)* for destinations
   outside of the EU, at least the customs tariff number and the export content type of
   the shipment are needed.
*  When using the *eCommerce Global Label API* for destinations outside of the origin
   country, at least the Terms Of Trade (Incoterms), the Customs Tariff Number (HS Code), and
   the product export description are needed.

The **export description** and the **tariff number** are taken from the respective **product
attributes**, see also `Additional Product Attributes`_. If the export description is not set,
the product name will be used instead.

The default values (e.g. Terms Of Trade) can be set in the module configuration.

Alternatively, you can enter the information by hand in the popup when creating the shipment,
e.g. for special cases with different, non-default information.

Everything else is the same as described in the section `National shipments`_.

.. admonition:: About configurable products

   For **configurable** products, the aforementioned attributes must be set directly in the configurable
   product, **not** in the associated simple products.

.. raw:: pdf

   PageBreak

Service selection
~~~~~~~~~~~~~~~~~

The available services for the current delivery address are shown in the packaging popup window.

The preselection of the services depends on the default values from the general
`Module configuration`_.

.. image:: images/en/merchant_services.png
   :scale: 50 %

.. admonition:: Note

   This screenshot is just an example. Other services than the ones shown here may be available.

Please note that the following inputs are **not** allowed for *Preferred location* and *Preferred neighbor*:

**Invalid special characters**

::

    < > \ ' " " + \n \r

**Invalid data**

* Paketbox
* Postfach
* Postfiliale / Postfiliale Direkt / Filiale / Filiale Direkt / Wunschfiliale
* Paketkasten
* DHL / Deutsche Post
* Packstation / P-A-C-K-S-T-A-T-I-O-N / Paketstation / Pack Station / P.A.C.K.S.T.A.T.I.O.N. /
  Pakcstation / Paackstation / Pakstation / Backstation / Bakstation / P A C K S T A T I O N

.. raw:: pdf

   PageBreak

Mass action
~~~~~~~~~~~

Shipments and labels can also be created using the mass action *Create Shipping Labels* in
the order grid:

* Sales → Orders → Mass action *Create Shipping Labels*

This allows the creation of shipping labels with no further user input

* for all items contained in the order
* with the services selected during checkout
* with the services selected in the *Automatic Shipment Creation* `Module configuration`_.

For international shipments, the customs information will be taken from the product attributes
and the default values in the configuration (see `International shipments`_), if necessary.

.. admonition:: Note

   The dropdown contains two very similar entries: *Print shipping labels* and *Create shipping labels*.
   Make sure to use the correct entry!

   The function *Print shipping labels* only allows printing **existing** shipping labels.

.. raw:: pdf

   PageBreak

Printing a shipping label
-------------------------

The successfully retrieved shipping labels can be opened in several locations
of the Admin Panel:

* Sales → Orders → Mass action *Print shipping labels*
* Sales → Shipments → Mass action *Print shipping labels*
* Detail page of a shipment → Button *Print shipping label*

This does not trigger the transmission to DHL, but only opens the labels again that
already exist. To transmit shipments to DHL, please use the `Mass action`_.

.. admonition:: Note

   If you are using a German locale, the exact names of the German menu entries
   *Bestellungen* or *Lieferscheine* can differ slightly, depending on the installed
   Language Pack (e.g. *Aufträge* or *Lieferungen*). However, this is not important
   for the usage.

.. raw:: pdf

   PageBreak

Printing a return slip
----------------------

When shipping within Germany, within Austria, or from Austria to Germany,
it is possible to create a return slip together with the shipping label.

Use the option *Retoure slip* when requesting a label in the packaging popup.

To book this service, make sure the `participation numbers`__ for returns are properly configured:

- Retoure DHL Paket (DE → DE)

__ `Account Data`_

.. raw:: pdf

   PageBreak

Canceling a shipment
--------------------

As long as a shipment has not been manifested, it can be canceled at DHL.

You can click the link *Delete* in the box *Shipping and tracking information* next
to the tracking number.

When using *DHL Business Customer Shipping*, this will also
cancel the shipment at DHL.

.. image:: images/en/shipping_and_tracking.png
   :scale: 75 %

.. admonition:: Note for eCommerce Global Label API

   If you are using the *eCommerce Global Label API* the above workflow will *not*
   cancel the shipment at DHL! It only deletes the tracking number in |mage|.

   To cancel an *eCommerce Global Label API* shipment, please use the usual way via
   the DHL website (e.g. the DHL Business Customer Portal).

   If you only delete the tracking number in |mage| without cancelling the shipment
   at DHL, you will be charged by DHL for the shipping cost.

.. raw:: pdf

   PageBreak

Automatic shipment creation
---------------------------

The process for creating shipments manually can be too time-consuming or
cumbersome for merchants with a high shipment volume. To make this easier,
you can automate the process of creating shipments and transmitting them to
DHL.

Enable the automatic shipment creation in the `Module configuration`_ and
select which services should be booked by default.

.. admonition:: Note

   The automatic shipment creation requires working |mage| cron jobs.

Every 15 minutes all orders which are ready for shipping (based on the configuration)
will be collected and transmitted to DHL.

If the transmission was successful, the label will be stored in |mage| and the
|mage| shipments will be created.

Error messages will be shown in the order comments.

.. raw:: pdf

   PageBreak

Shipment Overview
-----------------

In the order grid at *Sales → Orders* you will find a column *DHL Label Status*.
It displays the current status of your DHL shipments.

.. image:: images/en/label_status.png
   :scale: 50 %

The symbols have the following meaning:

- *colored DHL logo*: The DHL shipment was successfully created
- *grey DHL logo*: The DHL shipment was not yet created, or the order was only partially shipped
- *crossed-out DHL logo*: There was an error during the last attempt to create a DHL shipment

Shipments that cannot be processed by DHL Shipping will not display a logo in the DHL Label Status column.

You can filter orders by DHL label status using the *Filters* function above the order grid.

.. admonition:: Note: additional module required

   For this functionality, an additional module must be installed, see section `Installation`_.

   The add-on module cannot be installed in |mage| 2.1.x, therefore this functionality is **not supported**.

.. raw:: pdf

   PageBreak

Troubleshooting
---------------

Shipment creation
~~~~~~~~~~~~~~~~~

During the transmission of shipments to DHL, errors can occur. These are often
caused by an invalid address or an invalid combination of additional services.

When creating shipments manually, the error message will be directly visible in
the popup. You might have to scroll up inside the popup to see the message. If the
logging is enabled in the `Module Configuration`_, you can also check the shipments
in the log files.

.. admonition:: Note

   When using the automatic shipment creation, make sure to regularly check
   the status of your orders to prevent the repeated transmission of invalid
   shipment requests to DHL.

Erroneous shipment requests can be corrected as follows:

- In the popup window for selecting the package articles, you can correct invalid
  information.
- On the detail page of the order or shipment, you can edit the receiver address
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

Additional DHL services
~~~~~~~~~~~~~~~~~~~~~~~

In case of problems with `Additional Services In Checkout`_ (e.g. Preferred Day), error messages will be
written to a separate log file. See the notes in chapter `General settings`_. The log contains information
for further troubleshooting.

Also note the hints about `Booking additional services`_.

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
