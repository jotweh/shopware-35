<?php
// Shopware 3.0
// German language file

/*
/engine/connectors/saferpay/form.php
*/
if (is_object($payment)){
$sLang["saferpay"]["terms"] = $payment->sSYSTEM->sCONFIG['sSnippets']['sSaferpayTerms'];
$sLang["saferpay"]["info"] = $payment->sSYSTEM->sCONFIG['sSnippets']['sSaferpayInfo'];
$sLang["saferpay"]["continue"] = $payment->sSYSTEM->sCONFIG['sSnippets']['sSaferpayContinue'];
$sLang["saferpay"]["paymentMeanError"] = $payment->sSYSTEM->sCONFIG['sSnippets']['sSaferpayPaymentMeanError'];
$sLang["saferpay"]["testsystemError"] = $payment->sSYSTEM->sCONFIG['sSnippets']['sSaferpayTestsystemError'];
}
/*
/engine/connectors/saferpay/doPayment.php
*/
$sLang["saferpay"]["curlNotInstalled"] = "PHP-CURL ist nicht installiert oder nicht aktiviert!";
$sLang["saferpay"]["saferpayDescription"] = "Ihre Bestellung";
$sLang["saferpay"]["order"] = "Die Bestellung wurde bereits abgeschickt";
$sLang["saferpay"]["back"] = "zurueck";
$sLang["saferpay"]["curlNotWorking"] = "PHP-CURL kann keine ausgehende Aufrufe tätigen";

/*
/engine/connectors/saferpay/fail.php
*/
$sLang["saferpay"]["fail"] = "Ihre Saferpay Authorisierung war nicht erfolgreich.";
$sLang["saferpay"]["click"] = "Klicken Sie";
$sLang["saferpay"]["here"] = "hier";
$sLang["saferpay"]["checkout"] = "für die Zahlung mit Saferpay.";

/*
/engine/connectors/saferpay/doCheckoutPayment.php
*/
$sLang["saferpay"]["sKeyFailed"] = "OderID / SessionID Prüfung fehlgschlagen, mögliche Manipulation";
$sLang["saferpay"]["SaferpayID"] = "SaferpayID ist schon verbucht";
$sLang["saferpay"]["confirmationFailed"] = "Bestätigung fehlgeschlagen";
$sLang["saferpay"]["wrongAccountID"] = "Fehlerhafte Account-ID, mögliche Manipulation";
$sLang["saferpay"]["wrongAmount"] = "Fehlerhafter Gesamtsumme, mögliche Manipulation";
$sLang["saferpay"]["wrongCurrency"] = "Fehlerhafte Währung, mögliche Manipulation";
$sLang["saferpay"]["wrongOrder"] = "Fehlerhafte Order-ID,  mögliche Manipulation";
$sLang["saferpay"]["captureFailed"] = "Bestätigung OK - Capture fehlgeschlagen";
$sLang["saferpay"]["paymentBy"] = "Zahlung durch";
$sLang["saferpay"]["test"] = "(Testzahlung) -";
$sLang["saferpay"]["eci1"] = "ECI:1 - 3DSecure und Haftungsumkehr, Karteninhaber nimmt am Verfahren teil.";
$sLang["saferpay"]["eci2"] = "ECI:2 - 3DSecure und Haftungsumkehr, Karteninhaber nimmt nicht am Verfahren teil.";
$sLang["saferpay"]["eci"] = "ECI:";
$sLang["saferpay"]["liability"] = " - Keine Haftungsumkehr!";

/*
/engine/backend/modules/saferpayreserveorder/transactions.php
*/
$sLang["saferpayreserveorder"]["transactions_reorder"] = "Reorder TreePanel";
$sLang["saferpayreserveorder"]["transactions_status"] = "Status";
$sLang["saferpayreserveorder"]["transactions_no_orders_found"] = "Keine Bestellungen gefunden";
$sLang["saferpayreserveorder"]["transactions_date"] = "Datum";
$sLang["saferpayreserveorder"]["transactions_ordernumber"] = "Bestellnr.";
$sLang["saferpayreserveorder"]["transactions_Action"] = "Transaktion.";
$sLang["saferpayreserveorder"]["transactions_order_status"] = "Bestellstatus";
$sLang["saferpayreserveorder"]["transactions_payment_status"] = "Zahlstatus";
$sLang["saferpayreserveorder"]["transactions_total"] = "Gesamtbetrag";
$sLang["saferpayreserveorder"]["transactions_customer"] = "Kunde";
$sLang["saferpayreserveorder"]["transactions_options"] = "Optionen";
$sLang["saferpayreserveorder"]["transactions_PP_free"] = "Buchung";
$sLang["saferpayreserveorder"]["transactions_Period_end"] = "Frist abgelaufen";
$sLang["saferpayreserveorder"]["transactions_Evaluation_of"] = "Auswertung von:";
$sLang["saferpayreserveorder"]["transactions_Evaluation_until"] = "Auswertung bis:";
$sLang["saferpayreserveorder"]["transactions_Booking_Status"] = "Buchungsstatus:";
$sLang["saferpayreserveorder"]["transactions_show_all"] = "Alle anzeigen";
$sLang["saferpayreserveorder"]["transactions_open_bookings"] = "Offene Buchungen";
$sLang["saferpayreserveorder"]["transactions_Completed_bookings"] = "Abgeschlossene Buchungen";
$sLang["saferpayreserveorder"]["transactions_status_1"] = "Status:";
$sLang["saferpayreserveorder"]["transactions_status_payment"] = "Bezahlstatus:";
$sLang["saferpayreserveorder"]["transactions_search"] = "Suche (Nr./Transaktion)";
$sLang["saferpayreserveorder"]["transactions_refresh_view"] = "Ansicht aktualisieren";
$sLang["saferpayreserveorder"]["transactions_attention"] = "Hinweis: Durch Doppelklick auf den Status lässt sich dieser ändern. <br/ > Shopwware empfiehlt grundsätzlich reservierte Zahlungen nach spätestens 3 Tagen freizugeben (belasten).";
$sLang["saferpayreserveorder"]["transactions_total_in_period"] = "Gesamtumsatz in Zeitraum:";
$sLang["saferpayreserveorder"]["transactions_count_of_orders"] = "Anzahl Bestellungen:";
$sLang["saferpayreserveorder"]["transactions_cant_load_Description"] = "Beschreibung konnte nicht geladen werden";
$sLang["saferpayreserveorder"]["transactions_cant_load_orderID"] = "Bestell-ID konnte nicht ermittelt werden";
$sLang["saferpayreserveorder"]["transactions_cant_refresh_status"] = "Status konnte nicht aktualisiert werden";
$sLang["saferpayreserveorder"]["transactions_status_order"] = "Der Status der Bestellung";
$sLang["saferpayreserveorder"]["transactions_has_left"] = "wurde auf";
$sLang["saferpayreserveorder"]["transactions_changed"] = "geändert!";

/*
/engine/backend/modules/saferpayreserveorder/action/transactions.php
*/
$sLang["saferpayreserveorder"]["action_transaction_error_booking"] = "Es ist ein Fehler bei der Buchung aufgetreten";
$sLang["saferpayreserveorder"]["action_transaction_error_short_message"] = "Fehler";
$sLang["saferpayreserveorder"]["action_transaction_customer"] = "Kunde:";
$sLang["saferpayreserveorder"]["action_transaction_order_date"] = "Bestelldatum:";
$sLang["saferpayreserveorder"]["action_transaction_order_number"] = "Bestellnummer:";
$sLang["saferpayreserveorder"]["action_transaction_Transaction_number"] = "Transaktionsnr.:";
$sLang["saferpayreserveorder"]["action_transaction_total"] = "Gesamtbetrag:";
$sLang["saferpayreserveorder"]["action_transaction_payment"] = "Zahlverfahren:";
$sLang["saferpayreserveorder"]["action_transaction_Booking_already_done"] = "Buchung wurde durchgef&uuml;hrt!";
$sLang["saferpayreserveorder"]["action_transaction_no_order_found"] = "Keine Bestellung gefunden!";




?>