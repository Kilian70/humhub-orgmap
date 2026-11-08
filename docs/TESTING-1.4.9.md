# OrgMap 1.4.9 – Prüfstand

Stand: 08.11.2026. Basis ist die veröffentlichte Version 1.4.8.

Die Icon-Ausgabe verwendet nun durchgehend HumHubs Icon-Renderer. Die eigene
feste Font-Awesome-Liste wurde durch HumHubs Icon-Picker ersetzt. Dadurch stellt
HumHub 1.20 die Tabler Icons bereit, während vorhandene `fa-*`-Werte weiterhin
über HumHubs Kompatibilitätsschicht gelesen werden. JavaScript und Ansichten
greifen nicht mehr direkt auf Font-Awesome-Klassen zu.

Geprüfte Laufzeit: HumHub 1.19.3, PHP 8.3.30, MySQL 8.0.44,
Selenium 4.52.0 und Codeception 5.3.5.

- HumHub-Grundprüfung: 1 Test, 1 Assertion bestanden.
- OrgMap-Unit- und Integrationstests: 65 Tests, 356 Assertions bestanden.
- Icon-Kompatibilität: 2 Tests, 23 Assertions bestanden.
- OrgMap-Browsertest: 1 Test, 10 Assertions bestanden.
- PHP-Syntax und `git diff --check` bestanden.

HumHub 1.18 wird nicht mehr unterstützt; die Mindestversion ist 1.19. Eine
HumHub-1.20-Laufzeit war in der gemeinsamen lokalen Testumgebung noch nicht
vorhanden. Die 1.20-Anpassung wurde deshalb gegen die offizielle HumHub-
Icon-Schnittstelle umgesetzt und statisch geprüft, aber noch nicht in einer
echten 1.20-Installation ausgeführt.
