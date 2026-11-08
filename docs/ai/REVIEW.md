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
