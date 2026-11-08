# OrgMap 1.4.10 – Prüfstand

Stand: 08.11.2026. Diese Version ersetzt 1.4.9.

Die Umstellung verwendet HumHubs Icon-Renderer und offiziellen Icon-Picker.
Zusätzlich wurden alle verbliebenen JavaScript-Aufrufe des entfernten eigenen
Icon-Pickers beseitigt und der eigenständige Frontend-Test entsprechend
aktualisiert.

Geprüfte Laufzeit: HumHub 1.19.3, PHP 8.3.30, MySQL 8.0.44,
Selenium 4.52.0 und Codeception 5.3.5.

- HumHub-Grundprüfung: 1 Test, 1 Assertion bestanden.
- OrgMap-Unit- und Integrationstests: 65 Tests, 356 Assertions bestanden.
- Icon-Kompatibilität: 2 Tests, 23 Assertions bestanden.
- OrgMap-Browsertest: 1 Test, 10 Assertions bestanden.
- PHP-Syntax, Archivstruktur und `git diff --check` bestanden.

HumHub 1.18 wird nicht mehr unterstützt; die Mindestversion ist 1.19. Eine
HumHub-1.20-Laufzeit war lokal noch nicht vorhanden. Die Tabler-Anpassung nutzt
die offizielle HumHub-Icon-Schnittstelle.
