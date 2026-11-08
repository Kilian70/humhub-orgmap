# OrgMap – Hintergrund-Bedienhilfe

Stand:08.11.2026. Beauftragte gezielte Bedienverbesserung, kein vollständiger Audit.
Basis: stabile OrgMap1.4.7, GitHub-main Commit
170ba897ad02f80120fdee3e66152ae0fed507b4.
Veröffentlichtes Paket vom08.11.2026:
orgmap-1.4.7-humhub-1.18-1.19-20261108.zip,
SHA2561801f661e4b2aa78b4303685b1522832a8f79517f79b01531b362e6ffbe04a14.

## Ziel und Quellen

Lokaler Zielstand: HumHub1.19.3, PHP8.3.30, MySQL8.0.44, Firefox159.0.1.
Core v1.19.3: 54c5447961d05e319d124d8d000fe3ca560e9e44.
Offizielles Installationspaket SHA256:
012d7f53b6b3c8131ac8a33ee3231edd84deef5385aa3225be86733aa2d75a1a,
geprüft anhand https://api.humhub.com/v1/core/releases.

Gelesen am08.11.2026:
- Kilian70/humhub-ai-dev Commit3bfc6461247834453b0e261f7bd8dacd9da69f72:
  README, AGENTS, docs/REGELN, docs/QUELLEN, docs/VERSIONEN, docs/PRUEFUNG.
- humhub/humhub v1.19.3: CLAUDE.md, MIGRATE-DEV.md,
  docs/develop/module-migrate-1.19.md; passende Core-Klassen
  helpers/Html.php, widgets/form/ActiveForm.php, MigrateController,
  MigrationService, Codeception-Actor und User-LoginCest.
- Offizielles Beispiel humhub/example-basic, master am08.11.2026:
  module.json (minVersion1.18), views/index/index.php (Asset-/View-Konventionen).
- Gemeinsame Testregeln und drei Testanleitungen des lokalen Labors.

## Änderung und Grenzen

Nur views/admin/form.php ändert das Laufzeitverhalten: Warnung für verborgenes
gewähltes Bild, explizite Bildanzeige-Schaltfläche, Positionswarnung und klare
Beschriftungen. Keine automatische Korrektur beim Öffnen. Keine Änderung an
anderen Knoten, Datenbanken, Berechtigungen, Uploads, CSRF oder Migrationen.
Texte über Yii::t/Html::encode; Korrekturbutton type=button.
Die vorhandene POST-Aktion zum gemeinsamen Einpassen bleibt unverändert.

Einordnung: freiwillige Bedienverbesserung. Der Farbmodus funktioniert wie
vorgesehen; er ist kein belegter Core-API-Fehler. Die frühere abgeschnittene
Anordnung war live reproduzierbar. Die Hinweise machen solche Einstellungen
sichtbar, ohne bestehende Layouts automatisch umzubauen. Vue ist hierfür
nicht erforderlich. Andere Auditbereiche liegen ausserhalb dieses Auftrags.

Die aktuelle Veröffentlichung enthält die Laboranpassung ihrer alten Tests
bereits. Der eigene frühere Entwurf dieser Testanpassung wird nicht übernommen.
Neue Regressionstests prüfen die Formularhilfe im echten Firefox.
Version für die ausdrücklich beauftragte Veröffentlichung:1.4.8; Mindestversion unverändert.
Keine neue Laufzeitbehauptung für HumHub1.18.
