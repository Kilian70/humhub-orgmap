# Review: Hintergrund-Bedienhilfe 1.4.8

Regelgrundlage: Kilian70/humhub-ai-dev, Commit
3bfc6461247834453b0e261f7bd8dacd9da69f72.
Basis: stabile OrgMap1.4.7, Commit170ba897ad02f80120fdee3e66152ae0fed507b4.
Quellen und Umfang: PROJEKT.md. Tatsächliche Ergebnisse: ../TESTING-1.4.8.md.

Laufzeitänderung nur in views/admin/form.php: sichtbare Warnung bei verborgenem
gewähltem Bild und bei abgeschnittenen Elementen, explizite Bildkorrektur und
klare Beschriftungen. Keine automatische Korrektur beim Öffnen, kein automatisches
Speichern und keine Änderung anderer Knoten. Texte über Yii::t/Html::encode;
Korrekturbutton type=button. Berechtigungen, CSRF, Uploads und Migrationen
unverändert. Version1.4.8 ist eine Metadatenänderung gegenüber der getesteten Hilfe.

Neue Acceptance-Suite und Testactor prüfen die Hilfe im echten Firefox.
Die63 vorhandenen Modultests prüfen u.a. Gäste/Mitglieder/Verwalter,
POST-Grenzen, Uploadvalidierung, Schema und private Space-Verweise.

Befundklasse: freiwillige Bedienverbesserung. Farbmodus und Positionierung
sind kein belegter Widerspruch zu einer Core-API; die Hinweise verhindern
Missverständnisse. Kein Vue-Umbau und kein vollständiger Versionsaudit.

Manuelle Abnahme:
1. Administrator: Bild wählen, Nur Farbe setzen. Warnung erscheint; erst der
   Korrekturbutton wählt Nur Bild, Speichern bleibt erforderlich.
2. Administrator: Mittelpunkt0/0 setzen. Warnung erscheint; Zentrieren behebt
   die abgeschnittene Position bei einem ausreichend kleinen Element.
3. Administrator: Hintergrund aktivieren. Hinweis unterscheidet Einzelhintergrund
   und gesamte Karte; andere Elemente bleiben beim Bearbeiten erhalten.

1.18 wurde für diese Änderung auf Nutzerwunsch nicht erneut geprüft.
Keine Kompatibilitäts-/Sicherheitsbehauptung allein aus Syntaxprüfungen.
Private Sicherungen und Betriebsprotokolle werden nicht veröffentlicht.


## Explizite HumHub-1.19.4-Prüfung – 16.11.2026

Auf Nutzerauftrag wird die vorhandene GitHub-Prüfung um HumHub 1.19.4 am offiziellen Release-Commit `b8881d5ea7ee27384a33adeaa23100c73472c4ce` erweitert (PHP 8.2, 8.3). Bestehende Versionsprüfungen bleiben erhalten. Nur Workflow-Konfiguration, gegebenenfalls Prüfmetadaten und Dokumentation geändert; keine produktive Modul-PHP-, JavaScript-, Schema- oder Installationsänderung.

Quellen am 16.11.2026 geprüft: offizieller Tag v1.19.4 und Core-Konfiguration (Version 1.19.4), CLAUDE.md und docs/develop/module-migrate-1.19.md am obigen Commit. Keine neue HumHub-API eingeführt. Für Reservation bleibt Calendar 1.9.5 (`9d2a8b5da62e34a4ca2495edc6b1f6eb40e4a904`), für Messenger-Lesebestätigung das Originalmodul 3.4.6 (`f1f6af8e2fa19d40d21eace55091122269670552`) unverändert; beide Manifeste erlauben HumHub ab 1.19.

Commits, Pushes und PRs sind ausdrücklich freigegeben; Merge, Release und Deployment sind nicht Teil dieses Auftrags. Ausführung in kurzlebigen GitHub-Umgebungen mit synthetischen Daten. Keine produktive oder lokale MAMP-Installation angefasst. Die tatsächlich abgeschlossenen Tests und Grenzen werden in der PR-Beschreibung mit Run-Links dokumentiert; ein Syntaxcheck allein ist kein Kompatibilitätsnachweis.

Die bestehenden fachlichen Tests werden für die neue Zielversion unverändert ausgeführt. Keine Tests abgeschaltet und keine Modul-Mindestversion angehoben. Basiskontrollen der vorhandenen Workflows bleiben erhalten. Ergebnisse sind bis zum Abschluss der aktuellen PR-Checks ausdrücklich offen.
