# Archiv der historischen OrgMap Migrationen

Diese 35 PHP-Dateien sind bytegleich zum Modulcommit
`d24bdebc451a612041a64722662b29e9c9ab0de4` erhalten. Sie sind historische
Referenzen, kein ausführbarer Installations- oder Upgradepfad.

Die neue Migration im übergeordneten Verzeichnis ergänzt die aktuelle Struktur
ohne verwaiste Zeilen zu löschen. Vorhandene Einträge in der HumHub-Migrationstabelle
werden nicht gelöscht, umbenannt oder neu datiert.

Bei einem Upload über den alten Modulordner werden bekannte, bytegleich geprüfte
historische Restdateien vor der normalen Web-/Modulmigrationsliste automatisch
nach `overlaid/<eindeutige Kennung>/` verschoben. `manifest.json` hält die
SHA-256-Werte der 35 Originaldateien fest. Geänderte Dateien oder unsichere Pfade
werden nicht verschoben; die Aktualisierung stoppt zur manuellen Prüfung.
Die kanonischen Archivdateien bleiben unverändert. Die Rücksicherung muss auch
Moduldateien und dieses Archiv umfassen. Fehlende PHP-Schreibrechte müssen vorab
geklärt werden; die Schutzprüfung darf nicht deaktiviert werden.

Für globale Core-CLI-Migrationen zuerst im HumHub-Hauptverzeichnis
`php protected/yii orgmap-migration/prepare` ausführen, insbesondere unter 1.19
mit verzögertem Modul-Bootstrap. Dieser Vorabcheck führt keine Datenbankmigration aus.

Archivdateien niemals produktiv manuell ausführen, insbesondere nicht
`m260711_150000_harden_orgmap_relations.php` oder `uninstall.php`.
