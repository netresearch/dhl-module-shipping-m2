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
DHL Versenden M2: Paketversand für DHL Geschäftskunden
======================================================

Das Modul *DHL Versenden* für Magento® 2 ermöglicht es Händlern mit einem
DHL Geschäftskundenkonto, Sendungen über die DHL Geschäftskundenversand API
anzulegen und Versandscheine (Paketaufkleber) abzurufen.

.. raw:: pdf

   PageBreak

.. contents:: Endbenutzer-Dokumentation

   
Voraussetzungen
===============

Die nachfolgenden Voraussetzungen müssen für den reibungslosen Betrieb des Moduls erfüllt sein:

Magento®
--------

Folgende Magento®-Versionen werden vom Modul unterstützt:

- Community Edition 2.1.4 oder höher

PHP
---

Folgende PHP-Versionen werden vom Modul unterstützt:

- PHP 5.6.5 oder höher
- PHP 7.0.6 oder höher

Für die Anbindung des DHL Webservice muss die PHP SOAP Erweiterung auf dem
Webserver installiert und aktiviert sein.


Hinweise zur Verwendung des Moduls
==================================

Versandursprung und Währung
---------------------------

Die Extension *DHL Versenden* für Magento® 2 wendet sich an Händler mit Sitz in
Deutschland oder Österreich. Stellen Sie sicher, dass die Absenderadressen in den
drei im Abschnitt Modulkonfiguration_ genannten Bereichen korrekt ist.

Die Basiswährung der Installation wird als Euro angenommen. Es findet keine
Konvertierung aus anderen Währungen statt.

   
Installation und Konfiguration
==============================

Im Folgenden wird beschrieben, wie das Modul installiert wird und welche
Konfigurationseinstellungen vorgenommen werden müssen.

Installation
------------

Installieren Sie das Modul gemäß der Anweisung in der Datei README.md, die Sie im
Modulpackage finden. Achten Sie darauf, alle Anweisungen exakt zu befolgen und keine
Schritte zu überspringen.

Beim ersten Aufruf des Moduls werden diese neuen Adress-Attribute im System angelegt:

- ``dhl_versenden_info``

Die Attribute werden in folgenden Tabellen hinzugefügt:

- ``sales_flat_quote_address``
- ``sales_flat_order_address``

Modulkonfiguration
------------------

Für die Abwicklung von Versandaufträgen sind drei Konfigurationsbereiche relevant:

::

    Stores → Konfiguration → Allgemein → Allgemein → Store-Information
    Stores → Konfiguration → Verkäufe → Versandeinstellungen → Herkunft
    Stores → Konfiguration → Verkäufe → Versandarten → DHL Versenden

Stellen Sie sicher, dass die erforderlichen Felder aus den Bereichen
Store-Information und Herkunft ausgefüllt sind:

* Store-Information

  * Store-Name
  * Store-Kontakttelefon
* Herkunft

  * Land
  * Region/Bundesland
  * Postleitzahl
  * Stadt
  * Straße

Nachfolgens werden die Konfigurationsabschnitte für *DHL Versenden* beschrieben.

.. admonition:: Hinweis

   Der Abschnitt *Versandarten → DHL* ist Kernbestandteil von Magento® 2 und bindet
   die Schnittstelle von DHL USA an, nicht jedoch den DHL Geschäftskundenversand.

.. raw:: pdf

   PageBreak

Allgemeine Einstellungen
~~~~~~~~~~~~~~~~~~~~~~~~

Im Konfigurationsbereich *Allgemeine Einstellungen* wird festgelegt, ob der
*Sandbox-Modus* zum Testen der Integration verwendet oder die
Extension produktiv betrieben werden soll.

Darüber hinaus wird die Protokollierung konfiguriert. Wenn die Protokollierung
der *DHL Versenden* Extension sowie das allgemeine Logging
(*Stores → Konfiguration → Erweitert → Entwickleroptionen → Log Einstellungen*)
aktiviert sind, werden Webservice-Nachrichten in die Magento® Log-Dateien in ``var/log``
geschrieben. Es wird *keine* gesonderte Log-Datei für das Versenden-Modul erstellt.

Sie haben die Auswahl zwischen drei Protokollstufen:

* ``Error`` zeichnet Fehler in der Kommunikation zwischen Shop und DHL Webservice auf,
* ``Warning`` zeichnet Kommunikationsfehler sowie Fehler, die auf den Inhalt der
  Nachrichten zurückgehen (bspw. Adressvalidierung, ungültige Service-Auswahl), auf und
* ``Debug`` zeichnet sämtliche Nachrichten auf.

.. admonition:: Hinweis

   Stellen Sie sicher, dass die Log-Dateien regelmäßig bereinigt bzw. rotiert werden.

Stammdaten
~~~~~~~~~~

Im Konfigurationsbereich *Stammdaten* werden Ihre Zugangsdaten für den DHL Webservice
hinterlegt, die für den Produktivmodus erforderlich sind. Die Zugangsdaten erhalten
DHL Vertragskunden über den Vertrieb DHL Paket.

Tragen Sie folgende Daten ein:

* Benutzername (User)
* Passwort (Signature)
* EKP (DHL-Kundennummer, 10-stellig)
* Teilnahmenummern (Participation, jeweils zweistellig)

Die Eingabefelder erscheinen nur, wenn der Sanbox-Modus abgeschaltet wird.

Versandaufträge
~~~~~~~~~~~~~~~

Im Konfigurationsbereich *Versandaufträge* werden Einstellungen vorgenommen, die
für die Erteilung von Versandaufträgen über den DHL Webservice erforderlich sind.

* *Nur leitkodierbare Versandaufträge erteilen*: Ist diese Einstellung aktiviert,
  so werden nur Labels für seitens DHL erfolgreich validierte Lieferadressen erzeugt.
  Andernfalls wird DHL im Rahmen der Zustellung versuchen, fehlerhafte Lieferadressen
  korrekt zuzuordnen, wobei ein Nachkodierungsentgelt erhoben wird.
* *Versand in bestimmte Länder*: Hier wird festgelegt, ob der Versand in alle Länder
  möglich ist, die in der generellen Shop-Konfiguration freigeschaltet sind, oder nur
  in ausgewählte Länder.
* *Angezeigte Fehlermeldung*: Dieser Meldungstext wird angezeigt, wenn die Versandart
  nicht zur Verfügung steht.
* *Versandarten für DHL Versenden*: Legen Sie fest, welche Versandarten für die
  Versandkostenberechnung im Checkout verwendet werden sollen. Die hier ausgewählten
  Versandarten werden in der nachgelagerten Lieferscheinerstellung über den
  DHL Geschäftskundenversand abgewickelt.
* *Nachnahme-Zahlarten für DHL Versenden*: Legen Sie fest, bei welchen Zahlarten
  es sich um Nachnahme-Zahlarten handelt. Diese Information wird benötigt, um
  bei Bedarf den Nachnahmebetrag an den DHL Webservice zu übertragen.

Kontaktinformationen
~~~~~~~~~~~~~~~~~~~~

Im Konfigurationsbereich *Kontaktinformationen* legen Sie fest, welche Absenderdaten
während der Erstellung von Versandaufträgen an DHL übermittelt werden sollen.

Bankverbindung
~~~~~~~~~~~~~~

Im Konfigurationsbereich *Bankverbindung* legen Sie fest, welche Bankdaten im
Rahmen von Nachnahme-Versandaufträgen an DHL übermittelt werden.
Der vom Kunden erhobene Nachnahmebetrag wird auf dieses Konto transferiert.


Ablaufbeschreibung und Features
===============================

Annahme einer Bestellung
------------------------

Im Folgenden wird beschrieben, wie sich die Extension *DHL Versenden* in den
Bestellprozess integriert.

Checkout
~~~~~~~~

In der Modulkonfiguration_ wurden Versandarten für die Abwicklung der Versandaufträge
und die Erstellung der Paketaufkleber eingestellt. Wählt der Kunde im Checkout-Schritt
*Versandart* eine dieser DHL-Versandarten, kann die Bestellung im Nachgang über DHL
abgewickelt werden.

Im Checkout-Schritt *Zahlungsinformation* werden Nachnahme-Zahlungen deaktiviert,
falls der Nachnahme-Service für die gewählte Lieferadresse nicht zur Verfügung
steht.

Admin Order
~~~~~~~~~~~

Nachnahme-Zahlarten werden ebenso wie im Checkout deaktiviert, falls
der Nachnahme-Service für die gewählte Lieferadresse nicht zur Verfügung steht.

.. raw:: pdf

   PageBreak

Erstellen eines Versandauftrags
-------------------------------

Im Folgenden Abschnitt wird beschrieben, wie zu einer Bestellung ein Versandauftrag
erstellt und ein Paketaufkleber abgerufen wird.

Nationale Sendungen
~~~~~~~~~~~~~~~~~~~

Öffnen Sie im Admin Panel eine Bestellung, deren Versandart mit dem DHL
Geschäftskundenversand verknüpft ist. Betätigen Sie dann den Button *Versand*
im oberen Bereich der Seite.

.. image:: images/de/button_ship.png
   :scale: 75 %

Es öffnet sich die Seite *Neuer Versand für Bestellung*. Wählen Sie die Checkbox
*Paketaufkleber erstellen* an und betätigen Sie den Button *Lieferschein erstellen…*.

.. image:: images/de/button_submit_shipment.png
   :scale: 75 %

Es öffnet sich nun ein Popup zur Definition der im Paket enthaltenen Artikel.
Betätigen Sie den Button *Artikel hinzufügen*, markieren Sie die bestellten
Produkte und bestätigen Sie Ihre Auswahl durch Klick auf
*Gewählte Artikel zum Paket hinzufügen*. Die Angabe der Paketmaße ist optional.
Achten Sie auf das korrekte Paketgewicht.

.. admonition:: Hinweis

   Die Aufteilung der Produkte in mehrere Pakete wird vom DHL Webservice
   derzeit nicht unterstützt. Erstellen Sie alternativ mehrere Lieferscheine
   (Partial Shipments) zu einer Bestellung.

Der Button *OK* im Popup ist nun aktiviert. Bei Betätigung wird ein Versandauftrag
an DHL übermittelt und im Erfolgsfall der resultierende Paketaufkleber abgerufen.
Im Fehlerfall wird die vom Webservice erhaltene Fehlermeldung eingeblendet und
die Bestellung kann entsprechend korrigiert werden, siehe auch Fehlerbehandlung_.

.. raw:: pdf

   PageBreak

Internationale Sendungen
~~~~~~~~~~~~~~~~~~~~~~~~

Es können nur Sendungen innerhalb der EU verarbeitet werden, da keine Exportdokumente
(Zollpapiere) über die Extension erstellt werden können.

Gehen Sie ansonsten wie im Abschnitt `Nationale Sendungen`_ beschrieben vor.

Drucken eines Paketaufklebers
-----------------------------

Erfolgreich abgerufene Paketaufkleber können standardmäßig an verschiedenen
Stellen im Admin Panel eingesehen werden:

* Verkäufe → Bestellungen → Massenaktion *Paketaufkleber drucken*
* Verkäufe → Lieferscheine → Massenaktion *Paketaufkleber drucken*
* Detail-Ansicht eines Lieferscheins → Button *Paketaufkleber drucken*

Stornieren eines Versandauftrags
--------------------------------

Solange ein Versandauftrag nicht manifestiert ist, kann dieser über den DHL
Webservice storniert werden. Öffnen Sie dazu im Admin-Panel die Detail-Ansicht
eines Lieferscheins und betätigen Sie den Link *Löschen* in der Box
*Versand- und Trackinginformationen* neben der Sendungsnummer.

.. image:: images/de/shipping_and_tracking.png
   :scale: 75 %

Wenn der Versandauftrag erfolgreich über den DHL Webservice storniert wurde,
werden Sendungsnummer und Paketaufkleber aus dem System entfernt.

.. raw:: pdf

   PageBreak

Fehlerbehandlung
----------------

Während der Übertragung von Versandaufträgen an den DHL Webservice kann es zu
Fehlern bei der Erstellung eines Paketaufklebers kommen. Die Ursache dafür ist
in der Regel eine ungültige Liefer- bzw. Absenderadresse oder eine Fehlkonfiguration.

Bei der manuellen Erstellung von Versandaufträgen werden die vom Webservice
zurückgemeldete Fehlermeldung direkt angezeigt. Wenn die Protokollierung in der
Modulkonfiguration_ einschaltet ist, können Sie fehlerhafte Versandaufträge auch
in den Log-Dateien detailliert nachvollziehen.

Fehlerhafte Versandaufträge können wie folgt manuell korrigiert werden:

* Im Popup zur Definition der im Paket enthaltenen Artikel können ungültige
  Angaben korrigiert werden.
* In der Detail-Ansicht der Bestellung oder des Lieferscheins kann die
  Lieferadresse korrigiert werden. Betätigen Sie dazu den Link *Bearbeiten*
  in der Box *Versandadresse*.

  .. image:: images/de/edit_address_link.png
     :scale: 75 %

  Im nun angezeigten Formular können Sie im oberen
  Bereich die Standard-Felder der Lieferadresse bearbeiten und im unteren Bereich
  die zusätzlichen, für den DHL Geschäftskundenversand spezifischen Felder:

  * Straße (ohne Hausnummer)
  * Hausnummer
  * Adresszusatz


.. image:: images/de/edit_address_form.png
   :scale: 75 %

Speichern Sie anschließend die Adresse. Wurde die Fehlerursache behoben, so kann
das manuelle `Erstellen eines Versandauftrags`_ erneut durchgeführt werden.

Wurde ein Versandauftrag über den Webservice erfolgreich erstellt und sollen
dennoch nachträgliche Korrekturen vorgenommen werden, so stornieren Sie den
Versandauftrag wie im Abschnitt `Stornieren eines Versandauftrags`_ beschrieben
und betätigen Sie anschließend den Button *Paketaufkleber erstellen…* in
derselben Box *Versand- und Trackinginformationen*. Es gilt dasselbe Vorgehen
wie im Abschnitt `Erstellen eines Versandauftrags`_ beschrieben.


Modul deinstallieren oder deaktivieren
======================================

Gehen Sie wie folgt vor, um das Modul zu *deinstallieren*:

1. Löschen Sie alle Moduldateien aus dem Dateisystem.
2. Entfernen Sie die im Abschnitt `Installation`_ genannten Adressattribute.
3. Entfernen Sie den zum Modul gehörigen Eintrag ``dhl_versenden_setup`` aus der Tabelle ``core_resource``.
4. Entfernen Sie die zum Modul gehörigen Einträge ``carriers/dhlversenden/*`` aus der Tabelle ``core_config_data``.
5. Leeren Sie abschließend den Cache.

Das Modul wird *deaktiviert*, wenn der Knoten ``active`` in der Datei
``app/etc/modules/Dhl_Versenden.xml`` von **true** auf **false** abgeändert wird.


Technischer Support
===================

Wenn Sie Fragen haben oder auf Probleme stoßen, werfen Sie bitte zuerst einen Blick in das
Support-Portal (FAQ): http://dhl.support.netresearch.de/

Sollte sich das Problem damit nicht beheben lassen, können Sie das Supportteam über das o.g.
Portal oder per Mail unter dhl.support@netresearch.de kontaktieren.
