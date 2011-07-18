<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  2009-2011 Isotope eCommerce Workgroup
 * @author     Paul Kegel <paul@artified.nl>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */

$GLOBALS['TL_LANG']['tl_iso_payment_modules']['AUTH_CAPTURE'][1] = 'Transacties van dit type zullen worden verzonden voor autorisatie. Indien goedgekeurd zal de transactie automatisch worden opgenomen voor vereffening (verekening). Dit is het foutieve type transactie in de gateway (toegangspoort). Indien geen type wordt aangegeven bij het voorleggen van transacties aan de gateway, zal de gateway aannemen dat de transactie is van het type';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['AUTH_ONLY'][1] = 'Transacties van dit type worden gebruikt indien de handelaar de geldigheid van de credit card voor het bedrag van de verkochte goederen wil nagaan. Indien de handelaar geen goederen op voorraad heeft of orders wenst te herzien alvorens de goederen te verzenden moet dit type transactie worden gebruikt. De gateway zal dit type transactie versturen naar de financiële instelling voor goedkeuring. Deze transactie zal niet worden verzonden voor verrekening (akkoord). Indien de handelaar niet binnen 30 dagen op de transactie reageerd, zal de transactie niet langer beschikbaar zijn om te worden binnengehaald.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['CAPTURE_ONLY'][1] = 'Dit is een verzoek om een transactie aan te gaan waarvoor geen autorisatie was voorzien door de betalingsgateway. De gateway zal deze transactie accepteren als een autorisatiecode is ingediend. X-auth-code is een vereist veld voor CAPTURE_ONLY type transacties.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['CREDIT'][1] = 'Naar deze transactie wordt ook verwezen bij een terubetaling en geeft aan de gateway aan dat het geld van de handelaar naar de klant moet gaan. De gateway zal een terugbetalingsverzoek accepteren als de voorgelegde transactie voldoet aan de volgende voorwaarden:<ul><li>De transactie is verzonden met de ID van de originele transactie waartegenover de terugbetaling werd afgegeven.</li><li>De gateway heeft een record (registratie) van de originele transactie.</li><li>De originele transactie is verrekend.</li><li>De som van het bedrag van deze terugbetaling en alle overige terugbetalingen tegenover de originele transactie minder is dan het totale bedrag van de originele transactie.</li><li>Het volledige nummer (of de laatste vier cijfers) van het creditcard nummer waarmee de terubetaling is verzonden komt overeen met het volledige nummer (of de laatste vier cijfers) van het creditcard nummer waarmee de originale transactie werd gedaan.</li><li>De tergubetalingstransactie kan slechts plaats vinden binnen 120 dagen na de datum en tijd van de originele transactie.</li></ul> Een transactie key (sleutel) is vereist om een terugbetaling te verzenden naar het systeem.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['VOID'][1] = 'Deze betaling is een actie op een vorige betaling en wordt gebruikt om de voorgaande betaling ongedaan te maken en er zeker van te zijn dat deze niet wordt verrekend. Dit geldt voor ieder soort betaling (bijv.b. CREDIT, AUTH_CAPTURE,CAPTURE_ONLY, en AUTH_ONLY). De betaling zal door de gateway worden geaccepteerd als aan de volgende voorwaarden wordt voldaan:<ul><li>De betaling is voorzien van de ID van de betaling die ongedaan moet worden gemaakt.</li><li>De gateway heeft een record van de betaling waarnaar door de ID wordt verwezen.</li><li>De betaling is niet verrekend.</li></ul>';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['PRIOR_AUTH_CAPTURE'][1] = "Deze transactie wordt gebruikt voor het verzoek van verrekening van een betaling die eerder was verzonden als AUTH_ONLY. De gateway zal deze transactie accepteren en de verrekeing in gang zetten als aan de volgende voorwaarden is voldaan:<ul><li>Deze transactie is voorzien van de ID van de originele “authorization-only” transactie, die moet worden verrekend.</li><li>De transactie ID is geldig en het systeem heeft een record van de originele verzonden “authorization-only” transactie.</li><li>De originele transactie waarnaar verwezen wordt is nog niet verrekend, verlopen of fout gegaan.</li><li>Het bedrag dat wordt aangevraagd voor verrekening van deze transactie is minder dan of gelijk aan het oorspronkelijke geauthoriseerde bedrag.</li></ul> Als er geen bedrag bij deze transactie is ingediend, zal de gateway verrekening in gang zetten met het bedrag van de originele geauthoriseerde transactie.<em> Let op:\n Als een uitgebreide productomschrijving, belasting, vracht en/of belastinginformatie werd verzonden met de originele transactie, kan aangepaste informatie worden verzonden in het geval dat het transactiebedrag is gewijzigd.</em> Als geen aangepaste productomschrijving, belasting, vracht en/of belastinginformatie werd verzonden, zal de informatie van de originele transactie van toepassing zijn.";

