# OrgMap 1.4.8 – Prüfstand

Stand:08.11.2026. Basis ist die stabile1.4.7, Commit
170ba897ad02f80120fdee3e66152ae0fed507b4.

Geprüfte Laufzeit: HumHub1.19.3, PHP8.3.30, MySQL8.0.44,
Firefox159.0.1, Selenium4.52.0 und Codeception5.3.5.
Alle Läufe erfolgen mit dem gemeinsamen zentralen Runner auf wegwerfbaren
Dateikopien und getrennten automatischen Testdatenbanken.

- HumHub-Grundprüfung:1 Test,1 Assertion bestanden.
- OrgMap-Unit- und Integrationstests:63 Tests,333 Assertions bestanden.
- Firefox-/JavaScript-Grundprüfung:2 Tests,3 Assertions bestanden.
- Neue Formularhilfe:1 Browsertest,10 Assertions bestanden.
- PHP-Syntax der geänderten Dateien und git diff --check bestanden.

Der Formulartest prüft verborgene ausgewählte Bilder, bewusste Korrektur,
Bild+Farbe, leere Bildquellen, negative/leere Positionen, Zentrierung und
Hintergrundfüllung. Bestehende Werte werden nicht beim Öffnen verändert.
Die bestehende Berechtigungs-, Upload- und Migrationslogik bleibt unverändert.

Die Bedienhilfe wurde auf einer bestehenden Installation mit PHP8.3.36 und
MariaDB11.4.13 nach Datei-/SQL-Sicherung eingespielt. Browserprüfung und
Dateiprüfsummen erfolgreich; OrgMap-Inhalte und Zugriffsregeln unverändert.
Dies ist eine gezielte Live-Kontrolle, kein vollständiger Lauf der obigen Tests
auf MariaDB. Eine echte SQL-Rücksicherung wurde dabei nicht durchgeführt.

HumHub1.18 wurde auf ausdrücklichen Nutzerwunsch nicht erneut geprüft.
Die Mindestversion bleibt erhalten, eine neue1.18-Abnahme wird nicht behauptet.
Andere PHP-/Datenbankstände und externe Dateispeicher sind nicht abgedeckt.
Die GitHub-CI-Matrix beschreibt zusätzliche Läufe, deren Status separat gilt.

Zum Update das Installations-ZIP mit enthaltenem orgmap/-Ordner verwenden,
Datenbank und Dateien vorher sichern. Es gibt keine neue Migration gegenüber
1.4.7. Weitere Hinweise: ../INSTALLATION-UPGRADE.md.
