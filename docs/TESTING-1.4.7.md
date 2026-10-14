# OrgMap 1.4.7 – Prüfstand

Stand: 14.10.2026. Veröffentlichung als Vorabversion.

Getestet mit PHP 8.3.30 und MySQL 8.0.44: HumHub 1.18.6
(Core 870e35a44dd8ec03c659bc698e75a9a8ec244dc3) und 1.19.0
(Core 8f22a72361841404b3b442306126650fc0f8153b).

- Codeception: je 63 Tests und 333 Assertions bestanden.
- Abschliessendes Installations-ZIP: Neuinstallation und Upgrade mit synthetischen Bestandsdaten in isolierten Umgebungen; 605/641 Prüfungen auf 1.18.6 und 618/654 auf 1.19.0 bestanden (2518 insgesamt).
- Automatische Archivierung: 126 Dateisystemprüfungen bestanden.
- Labelkoordinaten: 27 Prüfungen; Baum-Ereignisse: 8 Prüfungen bestanden.
- Der Anwender bestätigte auf seiner cyon-Testinstanz Version 1.4.7, fehlerfreie Datenbankaktualisierung sowie funktionierende Karte, Baum und Bilder. Dies ist eine manuelle Rückmeldung.

Offen bleibt der Probelauf mit einer isolierten Kopie echter Produktivdaten und Dateien einschliesslich Rücksicherung. Andere PHP-/Datenbankversionen und externe Dateispeicher sind durch diese Ergebnisse nicht abgedeckt. Die CI-Matrix beschreibt zusätzliche geplante Prüfungen, keine bereits bestandenen Ergebnisse.

## Update

Vollständige Datenbank- und Dateisicherung erstellen. Nur das Installations-ZIP mit dem enthaltenen Ordner `orgmap/` verwenden; das Übergabearchiv ist kein Installationspaket. Hinweise: [Installation und Upgrade](../INSTALLATION-UPGRADE.md). Bekannte unveränderte alte Migrationsdateien werden vor der Modulmigration sicher archiviert. Geänderte Dateien und unsichere Pfade führen zu einem Abbruch. Für globale CLI-Updates zuerst `php protected/yii orgmap-migration/prepare` ausführen.

Das veröffentlichte Installations-ZIP bleibt exakt die geprüfte Fassung.
SHA-256: `d86aed57a137a913e00a0f85cdc022adbeaf6939d226937d6884c842a03f075e`.

Regelgrundlage: humhub-ai-dev, Commit `9854ea97c1db1c82e18c7c8dcc097810b6615a4f`.
