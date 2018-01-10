.. |date| date:: %d/%m/%Y
.. |year| date:: %Y
.. |mage| unicode:: Magento U+00AE
.. |mage2| replace:: |mage| 2

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

========================
DHL Shipping for |mage2|
========================

ChangeLog
=========

.. list-table::
   :header-rows: 1
   :widths: 2 2 10

   * - **Revision**
     - **Date**
     - **Description**

   * - 0.8.0
     - 10.01.2018
     - Added:

       * API support for postal facilities (Packstation, Postfiliale)
       * Cancel Business Customer Shipping labels
       * Cash On Delivery support for Global Shipping API labels

       Fixed:

       * Display separate tracking link for Global Shipping API labels
       * Rework product attribute uninstaller
