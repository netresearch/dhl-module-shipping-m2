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
DHL Shipping für |mage2|
========================

Das Modul *DHL Shipping* für |mage2| ermöglicht es Händlern mit einem DHL-Konto
Sendungsaufträge anzulegen und DHL-Versandscheine (Paketaufkleber) abzurufen.

Das Modul unterstützt die folgenden Schnittstellen:

* DHL Paket Geschäftskundenversand API (Business Customer Shipping)
* DHL eCommerce Global Shipping API

Die tatsächlich genutzte Schnittstelle hängt vom Absenderstandort (Land) ab.

.. raw:: pdf

   PageBreak

.. contents:: Endbenutzer-Dokumentation


Voraussetzungen
===============

Die nachfolgenden Voraussetzungen müssen für den reibungslosen Betrieb des Moduls erfüllt sein.

|mage2|
-------

Folgende |mage2|-Versionen werden vom aktuellen Modul unterstützt:

- Community Edition 2.2.4+
- Community Edition 2.3.0+

PHP
---

Folgende PHP-Versionen werden vom aktuellen Modul unterstützt:

- PHP 7.0.2
- PHP 7.0.4
- PHP 7.0.6+
- PHP 7.1.0+
- PHP 7.2.0+
- PHP 7.3.0+

Für die Anbindung des DHL Webservice muss die PHP SOAP Erweiterung auf dem
Webserver installiert und aktiviert sein.

Weitere Informationen finden Sie auch in diesen Dateien im Modulpackage bzw. Repository:

* README.md
* composer.json

Im Zweifelsfall sind die Versionsangaben in der Datei *composer.json* maßgeblich (Abschnitt "require").

.. admonition:: Repository

   Das öffentliche Git-Repository ist hier zu finden:
   
   https://github.com/netresearch/dhl-module-shipping-m2/

   README.md mit Installationsanleitung:

   https://github.com/netresearch/dhl-module-shipping-m2/blob/master/README.md


Hinweise zur Verwendung des Moduls
==================================

Versandursprung
---------------

Die DHL Schnittstellen (APIs) unterstützen ausschließlich folgende Absenderländer:

**DHL Geschäftskundenversand API (Business Customer Shipping)**

* Deutschland

.. ACHTUNG::
   Versand aus Österreich (AT) wird nicht länger unterstützt.

**eCommerce Global Label API**

* Australien
* Chile
* China
* Hongkong
* Indien
* Japan
* Kanada
* Malaysia
* Neuseeland
* Singapur
* Thailand
* USA
* Vietnam

Die Absenderadresse des Shops (Versandursprung) muss in einem der o.g. Länder liegen und
vollständig in die `Modulkonfiguration`_ eingetragen werden. Basierend auf dem Land wird
die entsprechende API automatisch gewählt.

Beachten Sie auch die Informationen in Abschnitt `Internationale Sendungen`_.

Währung
-------

Als Basiswährung wird die für das jeweilige Absenderland offiziell gültige Standardwährung
angenommen, die in der |mage2|-Konfiguration eingestellt sein muss. Es findet keine
automatische Konvertierung der Währungen statt.

Datenschutz
-----------

Durch das Modul werden personenbezogene Daten an DHL übermittelt, die zur Verarbeitung des Auftrags
erforderlich sind (Namen, Anschriften, Telefonnumern, E-Mail-Adressen, etc.). Der Umfang der
übermittelten Daten hängt von der `Modulkonfiguration`_ sowie den gewählten
`DHL Zusatzleistungen im Checkout`_ ab.

Der Händler muss sich vom Kunden das Einverständnis zur Verarbeitung der Daten einholen,
beispielsweise über die AGB des Shops bzw. eine Einverständniserklärung im Checkout (|mage2|
Checkout Agreements / Terms and Conditions).

.. raw:: pdf

   PageBreak

Installation und Konfiguration
==============================

Installation
------------

Installieren Sie das Modul gemäß der Anweisung in der Datei *README.md*, die Sie im
Modulpackage finden (siehe Abschnitt `Voraussetzungen`_).

Wir empfehlen die Installation mit Composer. Achten Sie darauf, alle Anweisungen exakt zu
befolgen und keine Schritte / Befehle zu überspringen.

Datenbank-Änderungen durch die Installation sind ebenfalls in der Datei *README.md* zu finden.

.. admonition:: Zusatzmodul für DHL Label-Status erforderlich

   Ab **Version 0.10.0** muss für die `Übersicht über offene und erstellte Sendungen`_
   das zusätzliche Modul `dhl/module-label-status <https://github.com/netresearch/dhl-module-label-status>`_
   installiert werden. Bei der Installation mit Composer wird dieses Zusatzmodul vorgeschlagen (suggested module).
   Es wird jedoch nicht standardmäßig installiert.
   
   Das Zusatzmodul kann nur in |mage| 2.2.x oder 2.3.x installiert werden. |mage| **2.1.x wird
   nicht unterstützt.** Es wird dann kein DHL Label-Status in der Bestellliste angezeigt.

Modulkonfiguration
------------------

Für die Abwicklung von Versandaufträgen sind drei Konfigurationsbereiche relevant:

::

    Stores → Konfiguration → Allgemein → Allgemein → Store-Information
    Stores → Konfiguration → Verkäufe → Versandeinstellungen → Herkunft
    Stores → Konfiguration → Verkäufe → Versandarten → DHL Shipping

Stellen Sie sicher, dass die erforderlichen Felder aus den Bereichen
*Store-Information* und *Herkunft* vollständig ausgefüllt sind:

* Store-Information

  * Store-Name
  * Store-Kontakttelefon

* Herkunft

  * Land
  * Region/Bundesland
  * Postleitzahl
  * Stadt
  * Straße

Wenn Sie aus mehreren Ländern versenden, können Sie auf Webseiten- bzw. Store-Ebene
abweichende Absenderadressen eintragen.

.. admonition:: Hinweis

   Der Abschnitt *Versandarten → DHL* ist Kernbestandteil von |mage2| und bindet
   die Schnittstelle von DHL USA an. Diese Einstellungen beziehen sich nicht auf die
   *DHL Shipping*-Extension.

Allgemeine Einstellungen
~~~~~~~~~~~~~~~~~~~~~~~~

Im Konfigurationsbereich *Allgemeine Einstellungen* wird angezeigt, welche der
zur Verfügung stehenden API-Anbindungen konfiguriert wird.

* DHL Business Customer Shipping (DE), oder
* DHL eCommerce Global Label API

Dieses Feld ist bereits gemäß dem eingestellten `Versandursprung`_ vorbelegt. Je nach
Auswahl erscheinen darunter unterschiedliche Konfigurationsfelder.

.. admonition:: Hinweis zur API

   Die tatsächlich verwendete API-Anbindung hängt vom `Versandursprung`_
   (Absenderadresse der Sendung) ab und wird bei der Übertragung an DHL automatisch
   gewählt. Das o.g. Dropdown macht lediglich die passenden Konfigurationsfelder sichtbar
   und stellt nicht ein, welche API genutzt wird.

Wählen Sie, ob der *Sandbox-Modus* zum Testen der Integration verwendet, oder die Extension
*produktiv* betrieben werden soll.

Die Einstellung *Protokollierung* aktiviert das Logging von Webservice-Nachrichten in die |mage2|
Log-Datei ``var/log/debug.log``. Es wird *keine gesonderte* Log-Datei für die DHL-Extension erstellt.
Beachten Sie außerdem diese `Hinweise zum Logging <http://dhl.support.netresearch.de/support/solutions/articles/12000051180>`_.

Sie haben die Auswahl zwischen drei Protokollstufen:

- *Error*: Zeichnet Kommunikationsfehler zwischen Shop und DHL Webservice auf.
- *Warning*: Zeichnet Kommunikationsfehler sowie Fehler aufgrund falscher Sendungsdaten
  (z.B. Adressvalidierung, ungültige Service-Auswahl), auf.
- *Debug*: Zeichnet sämtliche Nachrichten einschl. Paketaufkleber-Rohdaten im Log auf.

Stellen Sie sicher, dass die Log-Dateien regelmäßig archiviert bzw. rotiert werden. Die
Einstellung *Debug* sollte nur zur Problembehebung aktiviert werden, da die Log-Dateien
sonst mit der Zeit sehr groß werden.

.. raw:: pdf

   PageBreak

Stammdaten
~~~~~~~~~~

In diesem Konfigurationsbereich werden Ihre Zugangsdaten für den DHL Webservice
hinterlegt. Die Zugangsdaten erhalten Sie direkt von DHL.

Für die Nutzung des *DHL Geschäftskundenversands (Business Customer Shipping)*
im Sandbox-Modus sind keine Stammdaten erforderlich.

Für die Nutzung des *DHL Geschäftskundenversands (Business Customer Shipping)*
im Produktivbetrieb tragen Sie folgende Daten ein:

* Benutzername
* Passwort (Signature)
* DHL-Kundennummer (EKP), 10 stellig)
* Teilnahmenummern (jeweils zweistellig)

.. admonition:: Konfiguration der Abrechnungsnummern

   Eine detaillierte Anleitung zur Konfiguration der Abrechnungsnummern, DHL-Produkte und Teilnahmenummern finden Sie
   in diesem `Artikel in der Wissensdatenbank <http://dhl.support.netresearch.de/support/solutions/articles/12000024658>`_.

Zur Nutzung der *eCommerce Global Label API* tragen Sie stattdessen folgende Daten ein:

* Pickup Account Number (5-10 stellig)
* Customer Prefix (bis zu 5 Stellen)
* Distribution Center (6 stellig)
* Client ID
* Client Secret

Allgemeine Versandeinstellungen
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

- *Versandarten für DHL Versenden*: Legen Sie fest, welche Versandarten für die
  Versandkostenberechnung im Checkout verwendet werden sollen. Nur die hier ausgewählten
  Versandarten werden bei der Lieferscheinerstellung über die DHL-Extension abgewickelt.

.. raw:: pdf

   PageBreak

DHL Zusatzleistungen im Checkout
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Im Konfigurationsbereich *DHL Zusatzleistungen im Checkout* legen Sie fest,
welche Services Ihren Kunden angeboten werden.

Beachten Sie bitte auch die Hinweise zur `Buchbarkeit von Zusatzservices`_ sowie die
`Zusatzkosten für Services`_ und die Hinweise zum `Datenschutz`_.

* *Wunschort*: Der Kunde wählt einen alternativen Ablageort für seine Sendung,
  falls er nicht angetroffen wird.
* *Wunschnachbar*: Der Kunde wählt eine alternative Adresse in der Nachbarschaft
  für die Abgabe der Sendung, falls er nicht angetroffen wird.
* *Paketankündigung aktivieren*: Der Kunde wird per E-Mail von DHL über den Status seiner
  Sendung informiert. Hierzu wird die E-Mail-Adresse des Kunden an DHL übermittelt (siehe
  Hinweise zum `Datenschutz`_). Wählen Sie hier aus folgenden Optionen:

  * *Ja*: Der Kunde kann im Checkout wählen, ob der Service gebucht werden soll.
  * *Nein*: Im Checkout wird keine Auswahl angezeigt. Der Service wird nicht hinzugebucht.

* *Wunschtag*: Der Kunde wählt einen festgelegten Tag für seine Sendung,
  an welchem die Lieferung ankommen soll. Die verfügbaren Wunschtage werden dynamisch
  angezeigt, basierend auf der Empfängeradresse.
* *Wunschzeit*: Der Kunde wählt ein Zeitfenster für seine Sendung,
  in welchem die Lieferung ankommen soll. Die verfügbaren Wunschzeiten werden dynamisch
  angezeigt, basierend auf der Empfängeradresse.
* *Aufpreis für Wunschtag / Wunschzeit*: Dieser Betrag wird zu den Versandkosten
  hinzu addiert, wenn der Zusatzservice verwendet wird. Verwenden Sie Punkt statt Komma
  als Trennzeichen. Der Betrag muss in Brutto angegeben werden (einschl. Steuern).
  Wenn Sie die Zusatzkosten nicht an den Kunden weiterreichen wollen, tragen Sie hier
  ``0`` ein.
* *Wunschtag / Wunschzeit Serviceaufschlag Hinweistext*: Dieser Text wird dem Kunden
  im Checkout angezeigt, wenn der Zusatzservice ausgewählt wird. Sie können den
  Platzhalter ``$1`` im Text verwenden, welcher im Checkout durch den Zusatzbetrag
  und die Währung ersetzt wird.
* *Annahmeschluss*: Legt den Zeitpunkt fest, bis zu dem eingegangene Bestellungen
  noch am selben Tag abgeschickt werden. Bestellungen, die *nach* Annahmeschluss
  eingehen, werden nicht mehr am selben Tag verschickt. Der früheste Wunschtag
  verschiebt sich dann um einen Tag.
* *Tage ohne Paketübergabe*: Legen Sie fest, an welchen Tagen Sie *keine* Pakete an DHL
  übergeben. Hierdurch können die wählbaren Wunschtage beeinflusst werden.
* *Aufpreis für kombinierten Wunschtag und Wunschzeit*: Dieser Betrag wird zu
  den Versandkosten hinzu addiert, wenn *beide* Services gebucht werden. Verwenden Sie Punkt
  statt Komma als Trennzeichen. Der Betrag muss in Brutto angegeben werden (einschl. Steuern).
  Wenn Sie die Zusatzkosten nicht an den Kunden weiterreichen wollen, tragen Sie hier
  ``0`` ein.
* *Kombinierter Serviceaufschlag Hinweistext*: Dieser Text wird dem Kunden
  im Checkout angezeigt, wenn *beide* Zusatzservices ausgewählt sind. Sie können den
  Platzhalter ``$1`` im Text verwenden, welcher im Checkout durch den Zusatzbetrag
  und die Währung ersetzt wird.

.. raw:: pdf

   PageBreak

Nachnahme Einstellungen
~~~~~~~~~~~~~~~~~~~~~~~

* *Nachnahme-Zahlarten*: Legen Sie fest, bei welchen Zahlarten
  es sich um Nachnahme-Zahlarten handelt. Basierend darauf wird der Nachnahmebetrag
  an den DHL Webservice übertragen und Nachnahme-Label erzeugt. Wenn Nachnahme nicht
  nutzbar ist, werden diese Zahlarten im Checkout ausgeblendet.

* Legen Sie fest, welche *Bankdaten* für Nachnahme-Versandaufträge an DHL übermittelt
  werden. Der vom Empfänger erhobene Nachnahmebetrag wird auf dieses Konto transferiert.

  Beachten Sie, dass die Bankverbindung ggf. auch in Ihrem DHL-Konto hinterlegt werden
  muss. I.d.R. kann dies über das DHL Geschäftskundenportal erledigt werden.

Bei Nutzung der *eCommerce Global Label API* ist kein Nachnahmeversand möglich. Nachnahme-Zahlarten
werden dementsprechend im Checkout automatisch ausgeblendet.

Paketaufkleber Erstellung Standardeinstellungen
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In diesem Konfigurationsbereich legen Sie die Standardwerte für Sendungen fest.

Je nach gewählter API (DHL Business Customer Shipping, eCommerce Global Label API, ...) erscheinen
hier unterschiedliche Eingabemöglichkeiten.

* *Standardprodukt*: Hier werden die DHL Produkte angezeigt, die standardmäßig zur
  Erstellung von Versandaufträgen verwendet werden. Die Produkte hängen vom Absender-Standort ab
  und können deswegen hier nicht eingestellt werden. Beachten Sie die Hinweise im Abschnitt
  Modulkonfiguration_ zur Absenderadresse.
* *Standard Handelsklauseln*: Wählen Sie die Standard-Handelsklausel für die Zollabfertigung.
* *Standard Einlieferungsstelle*: Einlieferungstelle für Zollabfertigung.
* *Standard Zusatzentgelte*: Zusätzliche Entgelte für Zollabfertigung.
* *Standard Exportinhalt-Typ*: Inhalt der Sendung für Zollabfertigung.

Die Zollinformationen können auch über `Zusätzliche Produkt-Attribute`_ gesetzt werden, siehe auch
Abschnitt `Internationale Sendungen`_.

.. raw:: pdf

   PageBreak

Zusätzliche Versandservices
~~~~~~~~~~~~~~~~~~~~~~~~~~~
Diese Einstellungen gelten nur für Massenaktionen und automatisch erstellte Sendungen (Cronjob).

* *Nur leitkodierbare Versandaufträge erteilen*: Ist diese Einstellung aktiviert,
  wird DHL nur Sendungen akzeptieren, deren Adressen absolut korrekt sind. Ansonsten
  lehnt DHL die Sendung mit einer Fehlermeldung ab. Wenn diese Einstellung abgeschaltet
  ist, wird DHL versuchen, fehlerhafte Lieferadressen automatisch korrekt zuzuordnen,
  wofür ein Nachkodierungsentgelt erhoben wird. Wenn die Adresse überhaupt nicht
  zugeordnet werden kann, wird die Sendung dennoch abgelehnt.
* *Alterssichtprüfung aktivieren:* Wählen Sie, ob die Versandlabel das Vermerk zur Alterssichtprüfung
  tragen sollen, sowie welches Alter gelten soll. Auswahl:

  * *Nein*: Der Service wird nicht hinzugebucht.
  * *A16*: Mindestalter 16 Jahre.
  * *A18*: Mindestalter 18 Jahre.

* *Retourenbeileger aktivieren*: Wählen Sie, ob zum Versandauftrag auch ein Retourenbeileger
  erstellt werden soll. Siehe auch `Erstellen eines Retouren-Beilegers`_.
* *Zusätzlliche Transportversicherung aktivieren:* Wählen Sie, ob für den Versandauftrag eine Zusatzversicherung
  hinzugebucht werden soll.
* *Sperrgut aktivieren:* Wählen Sie, ob der Service *Sperrgut* hinzugebucht werden soll.

eCommerce Global API Versandeinstellungen
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Hier können Einstellungen zur Labelgröße, Seitengröße und Seitenlayout vorgenommen werden.

Dieser Abschnitt erscheint nur bei Nutzung der *eCommerce Global Label API*.

Automatische Sendungserstellung
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Im diesem Konfigurationsbereich legen Sie fest, ob automatisch Lieferscheine erzeugt
und Paketaufkleber abgerufen werden sollen.

Darüber hinaus können Sie bestimmen, welchen Bestell-Status eine Bestellung haben
muss, um während der automatischen Sendungserstellung berücksichtigt zu werden. Hierüber
können Sie steuern, welche Bestellungen von der automatischen Verarbeitung ausgeschlossen
werden sollen.

Außerdem legen Sie hier fest, ob eine E-Mail an den Käufer gesendet werden soll,
wenn der Lieferschein angelegt wurde. Hierbei handelt es sich um die
Versandbestätigung von |mage2|, nicht um die Paketankündigung von DHL.

.. admonition:: Hinweis

   Die automatische Sendungserstellung erfordert funktionierende |mage2| Cron Jobs.

.. raw:: pdf

   PageBreak

Zusätzliche Produkt-Attribute
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Das Modul führt die neuen Produkt-Attribute **Produktbeschreibung** (DHL Export Description)
und **Zolltarifnummer** (Tariff number) ein, welche für internationale Sendungen nutzbar
sind.

Diese Attribute können verwendet werden, um Zollinformationen fest im System zu hinterlegen,
so dass diese nicht bei jeder Sendung von Hand eingetragen werden müssen.

Beachten Sie die maximale Länge von:

 * 50 Zeichen für die Produktbeschreibung
 * 10 Zeichen für die Zolltarifnummer

Beachten Sie auch die Hinweise im Abschnitt `Internationale Sendungen`_.

Buchbarkeit von Zusatzservices
------------------------------

Die tatsächlich buchbaren Services sowie die wählbaren Wunschtage und Wunschzeiten hängen
von der Lieferadresse bzw. dem Zielland ab. Dazu wird die DHL Paketsteuerung API während
des Checkouts verwendet. Nicht verfügbare Services werden im Checkout
automatisch ausgeblendet.

Falls die Bestellung Artikel enthält, die nicht sofort lieferbar sind, ist keine Buchung
vom Wunschtag möglich.

Die gleichzeitige Buchung von Wunschort und Wunschnachbar ist nicht möglich.

Zusatzkosten für Services
-------------------------

Die Services *Wunschtag* und *Wunschzeit* sind **standardmäßig aktiviert!** Wenn diese
gebucht werden, werden die konfigurierten Service-Aufschläge zu den Versandkosten hinzugefügt.

Bei Nutzung der Versandart *Free Shipping / Versandkostenfrei* werden die eingestellten
Zusatzkosten generell außer Kraft gesetzt!

Wenn die Versandart *Table Rates / Tabellenbasierte Versandkosten* genutzt wird und eine
Grenze für kostenlosen Versand festgelegt werden soll, empfehlen wir dazu eine
Warenkorbpreisregel einzurichten. Durch Nutzung dieser Versandart bleiben die Aufpreise
für Zusatzservices erhalten.

Ablaufbeschreibung und Features
===============================

Annahme einer Bestellung
------------------------

Im Folgenden wird beschrieben, wie sich die DHL-Extension in den Bestellprozess integriert.

Bestellung über Checkout
~~~~~~~~~~~~~~~~~~~~~~~~

In der Modulkonfiguration_ wurden Versandarten für die Abwicklung der Versandaufträge
und die Erstellung der Paketaufkleber eingestellt. Wählt der Kunde im Checkout-Schritt
*Versandart* eine dieser DHL-Versandarten, kann die Lieferung im Nachgang über DHL
abgewickelt werden.

Im Checkout-Schritt *Zahlungsinformation* werden Nachnahme-Zahlungen automatisch
deaktiviert, falls der Nachnahme-Service nicht zur Verfügung steht (siehe `Nachnahme Einstellungen`_).

Bestellung über Admin Panel
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Nachnahme-Zahlarten werden ebenso wie im Checkout deaktiviert, falls der
Nachnahme-Service nicht zur Verfügung steht.

DHL Lieferadressen (Packstationen, Postfilialen)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Das Modul bietet eine eingeschränkte Unterstüzung von DHL Lieferadressen im Checkout:

* Das Format *Packstation 123* im Feld *Straße* wird erkannt.
* Das Format *Postfiliale 123* im Feld *Straße* wird erkannt.
* Ein numerischer Wert im Feld *Firma* wird als Postnummer erkannt.

.. admonition:: Hinweis

   Für die Übertragung an DHL ist die korrekte Schreibweise der o.g. Angaben entscheidend.

   Siehe auch `Versand an Filialen <https://www.dhl.de/de/privatkunden/pakete-empfangen/an-einem-abholort-empfangen/filiale-empfang.html>`_
   und `Versand an Packstationen <https://www.dhl.de/de/privatkunden/pakete-empfangen/an-einem-abholort-empfangen/packstation-empfang.html>`_.

.. raw:: pdf

   PageBreak

Erstellen eines Versandauftrags
-------------------------------

Im Folgenden Abschnitt wird beschrieben, wie zu einer Bestellung ein Versandauftrag
erstellt und ein Paketaufkleber abgerufen wird.

Nationale Sendungen
~~~~~~~~~~~~~~~~~~~

Öffnen Sie im Admin Panel eine Bestellung, deren Versandart mit dem DHL-Versand
verknüpft ist (siehe `Modulkonfiguration`_, Abschnitt *Versandarten für DHL Shipping*).

Betätigen Sie dann den Button *Versand* im oberen Bereich der Seite.

.. image:: images/de/button_ship.png
   :scale: 75 %

Es öffnet sich die Seite *Neuer Versand für Bestellung*.

Wählen Sie die Checkbox
*Paketaufkleber erstellen* an und betätigen Sie den Button *Lieferschein erstellen…*.

.. image:: images/de/button_submit_shipment.png
   :scale: 75 %

Es öffnet sich nun ein Popup zur Definition der im Paket enthaltenen Artikel. Das im
Abschnitt `Allgemeine Versandeinstellungen`_ eingestellte Standardprodukt ist hier
vorausgewählt.

Betätigen Sie den Button *Artikel hinzufügen*, markieren Sie *alle* Produkte und
bestätigen Sie Ihre Auswahl durch Klick auf *Gewählte Artikel zum Paket hinzufügen*.

Die Angabe der Paketmaße ist optional. Achten Sie auf das korrekte Paketgewicht.

Der Button *OK* im Popup ist nun aktiviert. Bei Betätigung wird ein Versandauftrag
an DHL übermittelt und im Erfolgsfall der resultierende Paketaufkleber abgerufen.

Im Fehlerfall wird eine Meldung am oberen Rand des Popups eingeblendet. Scrollen Sie
wenn nötig im Popup nach oben, falls die Fehlermeldung nicht sofort zu sehen ist.

Die Bestellung kann entsprechend korrigiert werden, siehe auch `Fehlerbehandlung`_.

.. raw:: pdf

   PageBreak

Internationale Sendungen
~~~~~~~~~~~~~~~~~~~~~~~~

Für internationale Sendungen sind unter bestimmten Umständen Zollinformationen notwendig.

Dabei gilt:

* Bei Nutzung des *DHL Geschäftskundenversands (Business Customer Shipping)* müssen für Ziele
  außerhalb der EU mindestens die Zolltarifnummern sowie der Inhaltstyp der Sendung angegeben
  werden.
* Bei Nutzung der *eCommerce Global Label API* müssen für Ziele außerhalb des Ursprungslandes
  mindestens die Zolltarifnummern, die Handelsklauseln und der Inhaltstyp der Sendung angegeben
  werden.

Die **Produktbeschreibung** (DHL Export Description) und **Zolltarifnummer** (Tariff number) werden
aus den gleichnamigen **Produkt-Attributen** übernommen, siehe auch
`Zusätzliche Produkt-Attribute`_. Wenn die Produktbeschreibung nicht gepflegt, ist wird stattdessen
der Produktname hierfür benutzt.

Standardwerte (z.B. Handelsklauseln) können in der Konfiguration des Moduls gesetzt werden.

Alternativ können die Angaben auch von Hand in das Popup zur Sendungserstellung eingegeben werden,
z.B. für Sonderfälle, die von den Standardwerten abweichen.

Gehen Sie ansonsten wie im Abschnitt `Nationale Sendungen`_ beschrieben vor.

.. admonition:: Besonderheit bei konfigurierbaren Produkten

   Bei **konfigurierbaren** Produkten (Configurable products) müssen die o.g. Attribute direkt am
   konfigurierbaren Produkt selbst gepflegt werden, **nicht** an den verknüpften einfachen Produkten
   (Simple products)!

.. raw:: pdf

   PageBreak

Service-Auswahl
~~~~~~~~~~~~~~~

Die für die aktuelle Lieferadresse möglichen Zusatzleistungen werden im Popup eingeblendet.

Die Vorauswahl der Services hängt von den Standardwerten in der allgemeinen
`Modulkonfiguration`_ ab.

.. image:: images/de/merchant_services.png
   :scale: 120 %

.. admonition:: Hinweis

   Dieser Screenshot ist nur ein Beispiel. Es stehen evtl. auch andere als die hier gezeigten
   Services zur Verfügung.

Beachten Sie, dass bei Wunschort oder Wunschnachbar folgende Angaben **nicht** zulässig sind:

**Unzulässige Sonderzeichen**

::

    < > \ ' " " + \n \r

**Unzulässige Angaben**

* Paketbox
* Postfach
* Postfiliale / Postfiliale Direkt / Filiale / Filiale Direkt / Wunschfiliale
* Paketkasten
* DHL / Deutsche Post
* Packstation / P-A-C-K-S-T-A-T-I-O-N / Paketstation / Pack Station / P.A.C.K.S.T.A.T.I.O.N. /
  Pakcstation / Paackstation / Pakstation / Backstation / Bakstation / P A C K S T A T I O N

Für den Versand an DHL-Abholorte (Packstation, Filiale, usw.) nutzen Sie bitte die dafür
vorgesehenen Adressfelder.

.. raw:: pdf

   PageBreak

Massenaktion
~~~~~~~~~~~~

Lieferscheine und Paketaufkleber können über die Massenaktion
*Paketaufkleber abrufen* in der Bestellübersicht erzeugt werden:

* Verkäufe → Bestellungen → Massenaktion *Paketaufkleber abrufen*

Dies ermöglicht es, einfache Paketaufkleber ohne manuelle Eingaben zu erstellen.
Dabei gilt:

* Es werden alle in der Bestellung enthaltenen Artikel übernommen.
* Die im Checkout gewählten DHL-Zusatzleistungen werden übernommen.
* Weitere Zusatzleistungen, die im Bereich *Automatische Sendungserstellung* in der
  Modulkonfiguration_ eingestellt sind, werden hinzugebucht.

Bei internationalen Sendungen werden wenn nötig die Zollinformationen aus den Produkt-Attributen
sowie aus den Standardwerten in der Konfiguration verwendet (siehe `Internationale Sendungen`_).

.. admonition:: Hinweis

   Im Dropdown sind zwei ähnliche Einträge zu finden: *Paketaufkleber abrufen* und *Paketaufkleber drucken*.
   Achten Sie darauf, den korrekten Eintrag zu nutzen!

   Die Funktion *Paketaufkleber drucken* ermöglicht lediglich den erneuten Ausdruck **bereits gespeicherter** DHL-Label.

.. raw:: pdf

   PageBreak

Drucken eines Paketaufklebers
-----------------------------

Erfolgreich abgerufene Paketaufkleber können an verschiedenen Stellen im Admin Panel
eingesehen werden:

* Verkäufe → Bestellungen → Massenaktion *Paketaufkleber drucken*
* Verkäufe → Lieferscheine → Massenaktion *Paketaufkleber drucken*
* Detail-Ansicht eines Lieferscheins → Button *Paketaufkleber drucken*

Hierdurch wird keine Übertragung an DHL durchgeführt, sondern lediglich die bereits
vorliegenden Label nochmal ausgegeben. Um die Übertragung auszuführen, nutzen Sie
stattdessen die `Massenaktion`_.

.. admonition:: Hinweis

   Die exakte Bezeichnung der Menüpunkte *Bestellungen* bzw. *Lieferscheine* kann je
   nach installiertem Language Pack leicht abweichen (z.B. *Aufträge* oder *Lieferungen*).
   Das ist aber für die weitere Nutzung unerheblich.

.. raw:: pdf

   PageBreak

Erstellen eines Retouren-Beilegers
----------------------------------

Bei Versand innerhalb Deutschlands, innerhalb Österreichs oder von Österreich
nach Deutschland ist es möglich, gemeinsam mit dem Paketaufkleber einen
Retouren-Beileger zu beauftragen.

Nutzen Sie dafür beim Erstellen des Labels im Popup das Auswahlfeld *Retouren-Beileger*.

Stellen Sie sicher, dass die `Teilnahmenummern`__ für Retouren korrekt konfiguriert sind:

- Retoure DHL Paket (DE → DE)

__ `Stammdaten`_

.. raw:: pdf

   PageBreak

Stornieren eines Versandauftrags
--------------------------------

Solange ein Versandauftrag nicht manifestiert ist, kann dieser bei DHL storniert werden.

Sie können den Link *Löschen* in der Box *Versand- und Trackinginformationen* neben der
Sendungsnummer anklicken.

Bei Nutzung des *DHL Geschäftskundenversands (Business Customer Shipping)* wird hierdurch
auch der Auftrag bei DHL storniert.

.. image:: images/de/shipping_and_tracking.png
   :scale: 75 %

.. admonition:: Hinweis zur eCommerce Global Label API

   Bei Nutzung der *eCommerce Global Label API* wird über den oben beschriebenen Weg der
   Auftrag bei DHL *nicht* storniert! Es wird lediglich die Trackingnummer aus |mage2| entfernt.

   Zur Stornierung eines *eCommerce Global Label API* Versandauftrags nutzen Sie bitte den
   Ihnen bekannten Zugang über die DHL Website (z.B. das Geschäftskundenportal).

   Wenn lediglich die Trackingnummer aus |mage2| entfernt wird, ohne den Auftrag bei
   DHL zu stornieren, wird DHL diesen in Rechnung stellen.

.. raw:: pdf

   PageBreak

Automatische Sendungserstellung
-------------------------------

Der manuelle Prozess zur Erstellung von Versandaufträgen ist insbesondere für
Händler mit hohem Versandvolumen sehr zeitaufwendig und unkomfortabel. Um den
Abruf von Paketaufklebern zu erleichtern, können Sie das Erstellen von
Lieferscheinen und Versandaufträgen automatisieren.

Aktivieren Sie dazu in der Modulkonfiguration_ die automatische Sendungserstellung
und legen Sie fest, welche Zusatzleistungen für alle automatisch erzeugten Versandaufträge
hinzugebucht werden sollen.

.. admonition:: Hinweis

   Die automatische Sendungserstellung erfordert funktionierende |mage2| Cron Jobs.

Im Abstand von 15 Minuten werden alle versandbereiten Bestellungen (gemäß den
getroffenen Einstellungen) gesammelt und an DHL übermittelt.

Bei erfolgreicher Übertragung werden die DHL-Label in |mage2| gespeichert und die
Lieferscheine erstellt.

Im Fehlerfall sehen Sie die entsprechende Meldung in den Bestellkommentaren.

.. raw:: pdf

   PageBreak

Übersicht über offene und erstellte Sendungen
---------------------------------------------

Unter *Verkäufe → Bestellungen* finden Sie eine Spalte *DHL Label Status*.
Dort wird der aktuelle Zustand Ihrer DHL-Sendungen abgebildet.

.. image:: images/de/label_status.png
  :scale: 75 %

Die Symbole haben folgende Bedeutung:

- *DHL-Logo farbig*: Die DHL-Sendung wurde erfolgreich erstellt
- *DHL-Logo ausgegraut*: Die DHL-Sendung wurde noch nicht oder nur teilweise erstellt
- *DHL-Logo durchgestrichen*: Beim Erstellen der DHL-Sendung ist zuletzt ein Fehler aufgetreten

Bei Sendungen, die nicht mit DHL Shipping verarbeitet werden können, wird kein Logo angezeigt.

Über die Funktion *Filter* in der Bestellübersicht lassen sich Bestellungen nach den verschiedenen Labelstati filtern.

.. admonition:: Bitte beachten: Zusatzmodul erforderlich

   Für diese Funktion muss ein zusätzliches Modul installiert werden, siehe Abschnitt `Installation`_.
   
   In |mage| 2.1.x kann das Zusatzmodul nicht installiert werden, daher wird diese Funktion darin **nicht unterstützt**.

.. raw:: pdf

   PageBreak

Fehlerbehandlung
----------------

Während der Übertragung von Versandaufträgen an den DHL Webservice kann es zu
Fehlern bei der Erstellung eines Paketaufklebers kommen. Die Ursache dafür ist
in der Regel eine ungültige Liefer- bzw. Absenderadresse oder eine Fehlkonfiguration.

Bei der manuellen Erstellung von Versandaufträgen werden die vom Webservice
zurückgemeldete Fehlermeldung direkt im Popup angezeigt. Scrollen Sie ggf. im Popup
nach oben, um die Meldung zu sehen.

Wenn die Protokollierung in der Modulkonfiguration_ einschaltet ist, können Sie
fehlerhafte Versandaufträge auch in den Log-Dateien detailliert nachvollziehen.

Fehlerhafte Versandaufträge können wie folgt manuell korrigiert werden:

* Im Popup zur Definition der im Paket enthaltenen Artikel können ungültige
  Angaben korrigiert werden.
* In der Detail-Ansicht der Bestellung oder des Lieferscheins kann die
  Lieferadresse korrigiert werden. Betätigen Sie dazu den Link *Bearbeiten*
  in der Box *Versandadresse*.

  .. image:: images/de/edit_address_link.png
     :scale: 75 %

  Im nun angezeigten Formular können Sie im oberen Bereich die Standard-Felder
  der Lieferadresse bearbeiten und im unteren Bereich die zusätzlichen
  DHL-spezifischen Felder:

  * Straße (ohne Hausnummer)
  * Hausnummer (einzeln)
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

.. raw:: pdf

   PageBreak

Modul deinstallieren
====================

Befolgen Sie die Anleitung aus der Datei *README.md* im Modulpackage, um das
Modul zu deinstallieren.

Die Datei *README.md* ist im Abschnitt `Voraussetzungen`_ verlinkt.


Technischer Support
===================

Wenn Sie Fragen haben oder auf Probleme stoßen, werfen Sie bitte zuerst einen Blick in das
Support-Portal (FAQ): http://dhl.support.netresearch.de/

Sollte sich das Problem damit nicht beheben lassen, können Sie das Supportteam über das o.g.
Portal oder per Mail unter dhl.support@netresearch.de kontaktieren.
