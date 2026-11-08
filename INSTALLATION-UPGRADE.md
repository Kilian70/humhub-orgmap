# OrgMap – Installation, Update und Sicherung

Modulversion **1.4.8**, stabile Veröffentlichung vom 08.11.2026.
Diese Version ergänzt die Hintergrund-Bedienhilfe und benötigt keine neue
Datenbankmigration gegenüber 1.4.7. Geprüft: **HumHub 1.19.3**, PHP 8.3.30,
MySQL 8.0.44 und Firefox 159.0.1 im gemeinsamen isolierten Testlabor.
Die Mindestversion 1.18 bleibt unverändert. Auf ausdrücklichen Nutzerwunsch
wurde HumHub 1.18 für diese Änderung nicht erneut geprüft; daraus folgt keine
neue 1.18-Abnahme. Weitere PHP-/Datenbankstände und externe Dateispeicher
sind durch diese Läufe nicht abgedeckt.

## Vorher sichern

- Tatsächliche HumHub-/PHP-Version und Zielinstallation kontrollieren; zuerst
  an einer separaten Kopie mit den eigenen Bestandsdaten testen.
- Vollständige Datenbank einschließlich Moduleinstellungen und Migrationshistorie,
  HumHub-Konfiguration, gesamten alten Modulordner, Uploads und gegebenenfalls
  externen Core-Dateispeicher sichern. Auch `uploads/orgmap/assets` und ein
  vorhandenes privates OrgMap-Altdateiarchiv einschließen.
- Sicherungen außerhalb des Webroots aufbewahren und ihre Wiederherstellung
  prüfen. Schreibzugriffe während Sicherung und Update pausieren, damit
  Datenbank und Dateien denselben Stand haben.

## Neuinstallation

1. ZIP zunächst außerhalb des Webroots entpacken. `orgmap/module.json` muss
   unmittelbar im entpackten Modulordner liegen.
2. Den vollständigen Ordner `orgmap` nach `protected/modules/orgmap` kopieren;
   keine zusätzliche Verschachtelung `orgmap/orgmap` anlegen.
3. Über **Administration → Module** aktivieren. HumHub führt dabei die
   notwendigen Modulmigrationen aus; vorher die Zielinstallation freigeben.
4. OrgMap konfigurieren und die globalen Lese-/Verwaltungsrechte passend setzen.
   Karte, Baum, Uploads und Zugriff mit einem Leser und Administrator prüfen.

## Bestehende Installation aktualisieren

1. Nach der Sicherung lokale Modulanpassungen vergleichen. Das vollständige neue
   Modul hochladen; ein Upload über den alten 1.4.5-Ordner ist für die bekannten
   historischen Migrationen unterstützt. Nicht deinstallieren und keine Tabellen löschen.
2. Vor normalen Web-/Modulmigrationen archiviert der neue Updateweg die 35 bekannten,
   bytegleich verifizierten Altdateien automatisch unter
   `migrations/legacy/overlaid/<eindeutige Kennung>/`. Die kanonischen Archivdateien
   bleiben unverändert; keine Originaldatei wird verworfen oder überschrieben.
   Danach verbleiben direkt unter `migrations/` nur die beiden neuen Migrationen.
   Geänderte Altdateien, Symlinks, ein fehlendes Manifest oder fehlende Schreibrechte
   führen zum Abbruch. Dann lokale Änderungen prüfen bzw. PHP-Schreibrechte für
   Migrationsverzeichnis und Archiv korrigieren, nicht die Schutzprüfung abschalten.
   Bei einem globalen CLI-Update **vorher** im HumHub-Hauptverzeichnis ausführen:
   `php protected/yii orgmap-migration/prepare`. Dies archiviert nur Dateien und
   führt keine Datenbankmigration aus. Insbesondere HumHub 1.19 überspringt beim
   globalen CLI-Migrationsstart den üblichen Modul-Bootstrap; dort nicht auf den
   Web-Hook vertrauen. Erst nach erfolgreichem Vorabcheck den CLI-Updateweg starten.
3. Vor dem Update privaten Altdateiarchivpfad und Schreibrechte prüfen:
   Standard `@app/orgmap-legacy-assets`, konfigurierbar über
   `Module::legacyAssetArchivePath`. Der Pfad darf nicht öffentlich erreichbar
   sein; atomare Umbenennung vom alten Assetverzeichnis muss möglich sein.
   Gleichzeitige präfixierte und unpräfixierte OrgMap-Tabellen vorab klären;
   die Migration vereinigt oder löscht sie nicht.
4. Erst dann den regulären HumHub-Modul-/Datenbank-Updateweg für die ausstehenden
   OrgMap-Migrationen ausführen. Bei einer Neuinstallation oder älteren Version
   können beide Migrationen anstehen; nach bereits erfolgter Secure-Storage-
   Migration nur die Folgemigration. Die Manifestversion allein bestätigt
   nicht, dass keine Migration aussteht. Den Erfolg in der Migrationshistorie
   prüfen und keine ausstehenden Fremdmodulmigrationen ungeprüft mit ausführen.
5. Die erste Migration ergänzt das Schema, überträgt alte Bildbytes geprüft in
   den Core-Speicher und archiviert das alte öffentliche Assetverzeichnis privat,
   einschließlich nicht referenzierter Dateien. Sie löscht dieses Archiv nicht.
   Die Folgemigration entfernt automatische Löschwirkungen der Space-Beziehung;
   vorhandene Node-Zuordnungen bleiben erhalten. Fehler oder zurückgestellte
   Constraints anhand der Meldungen prüfen, nicht durch alte Migrationen reparieren.
6. Veröffentlichten OrgMap-Assetcache mit dem vorgesehenen HumHub-Verfahren
   erneuern, Browser neu laden und kontrollieren, dass das neue `interaction.js`
   verwendet wird. Karte/Baum, vorhandene Bilder, neue Uploads und Rechte prüfen;
   ALT-Ziehen beider Labeltypen bei 50 % und 200 % einschließlich Speichern und
   Neuladen testen. Space-Löschung ausschließlich mit neuen Testdaten prüfen.

## Bei Fehlern / Rücksicherung

Update anhalten und Logs sichern. MySQL-Schemaänderungen sind nicht vollständig
transaktional; Teilfortschritt kann bestehen bleiben. Die neuen Migrationen
verweigern einen destruktiven `down()`-Rückbau. Ein Zurückkopieren alten Codes
allein genügt nicht: Datenbank, Code, Konfiguration und Dateispeicher aus der
zusammengehörigen geprüften Sicherung wiederherstellen. Private Archive erhalten;
bei einem erneuten Versuch ihren Zustand zuerst prüfen. Bereits vor dem Update
verlorene Space-Zuordnungen können nicht aus fehlenden Daten rekonstruiert werden.

## Paketinhalt und Nachweise

Das Paket enthält sämtliche Laufzeitdateien, Lizenz, Changelog, diese Hinweise
und das vollständige historische Migrationsarchiv. Entwicklungsumgebung,
Git-Metadaten, interne Prüfberichte, Testfixtures, Zugangsdaten und lokale
Sicherungen sind nicht Teil des installierbaren Moduls. `PACKAGE-MANIFEST.json`
enthält die SHA-256-Prüfsummen der Paketdateien; die beigefügte `.sha256`-Datei
prüft das ZIP. Die Manifestdatei selbst ist nicht in ihrer eigenen Prüfliste.

Vorhandene Nachweise: Codeception je Core 63 Tests / 333 Assertions bestanden;
installierte Labelskripte je 27 Prüfungen bestanden; lokale Migrationen und
P1-Browserprüfung bestanden; manuelle ALT-Prüfung beider Labeltypen bei 50 % / 200 %
einschließlich Speichern/Neuladen vom Nutzer am 13.10.2026 als bestanden bestätigt.
Das Paket wurde zusätzlich auf Vollständigkeit, Archivstruktur, Prüfsummen und
PHP-/JavaScript-Syntax kontrolliert. Seine Erstellung führt keine Migration aus.
