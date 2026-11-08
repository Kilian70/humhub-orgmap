# ORGMAP -- Interactive Organization Map for HumHub

## TL;DR

-   Interactive organization map for HumHub
-   Flexible nodes and SVG-based connections
-   Tree, Map and Split views
-   Drag & Drop, Zoom & Pan
-   Assets, background nodes and reusable images
-   German and English translations
-   Dark mode support
-   Accessible keyboard navigation and screen reader support

------------------------------------------------------------------------

# ORGMAP

**Version:** 1.4.9\
**Author & Maintainer:** Kilian Schmid\
**Supported release lines:** HumHub 1.19 and 1.20\
**License:** GNU Affero General Public License v3.0 or later (`AGPL-3.0-or-later`)

------------------------------------------------------------------------

# Description

ORGMAP is a HumHub module for creating interactive organization maps.

Unlike a traditional organization chart, ORGMAP provides a flexible
visual workspace where organizations, spaces, external resources and
custom relationships can be displayed and managed.

------------------------------------------------------------------------

# Main Features

## Interactive Workspace

-   Zoom
-   Pan
-   Drag & Drop
-   Node resizing
-   Automatic centering
-   Adjustable workspace size
-   Background images
-   Fullscreen presentation mode
-   Print and PDF output

## Nodes

Each node supports:

-   Title
-   Subtitle
-   Color
-   Icon
-   Asset image
-   Custom image
-   Visibility
-   Position
-   Size
-   Opacity
-   Separate color and image opacity
-   URL
-   Labels

Supported node types:

-   Organization
-   Space
-   External Entry
-   Background
-   Panel
-   Legend

## Connections

SVG-based connections support:

-   Multiple connection types
-   Custom colors
-   Line width
-   Line style
-   Arrow heads
-   Curved lines
-   Labels
-   Font settings
-   Label rotation
-   Free label positioning

Default connection types:

-   Reports To
-   Coordinates
-   Supports
-   Belongs To
-   Collaborates
-   Decides
-   Informs
-   Custom

## Assets

Reusable assets:

-   Images
-   Icons
-   Backgrounds
-   Panels

------------------------------------------------------------------------

# Views

## Tree View

Hierarchical navigation with search, keyboard control and accessible focus
indication.

## Map View

Interactive workspace.

## Split View

Tree and workspace displayed together.

------------------------------------------------------------------------

# Administration

The administration area includes:

-   Node management
-   Organization management
-   Asset management
-   Connection editor
-   Workspace settings
-   Guest access
-   Module settings

------------------------------------------------------------------------

# Installation

1.  Copy the module to:

``` text
protected/modules/orgmap
```

2.  Enable the module:

Administration → Modules

3.  Configure the module:

Administration → ORGMAP

4.  Before updating an existing installation, back up the database, configuration,
    complete module directory and file storage. Verified historical migration files
    left by an overlay upload are now automatically archived before normal web/module
    migrations are enumerated. Changed files or unsafe paths stop the update.
    For global CLI migrations, run the file-only preflight first:
    `php protected/yii orgmap-migration/prepare`. Then use the regular HumHub update process.

See [installation, upgrade and backup instructions](INSTALLATION-UPGRADE.md).

## Printing

For the most reliable PDF and physical print output, use a Chromium-based
browser such as Google Chrome. Firefox may render complex, transparent map
layers differently depending on the installed printer driver.

------------------------------------------------------------------------

# Configuration

Available settings include:

-   Module title
-   Workspace size
-   Workspace dimensions
-   Guest access
-   Main-menu visibility (hidden, administrators only, or all users)
-   Top menu position
-   Connection visibility
-   Workspace fitting to the background image

------------------------------------------------------------------------

# Permissions

  Permission     Description
  -------------- -------------------------------------------------------
  ViewOrgMap     View the organization map
  ManageOrgMap   Create, edit and delete nodes, assets and connections

------------------------------------------------------------------------

# Internationalization

Translation files:

``` text
messages/de/base.php
messages/en/base.php
```

Use Yii translations for every user-facing string:

``` php
Yii::t('OrgmapModule.base', 'Text')
```

------------------------------------------------------------------------

# Project Structure

``` text
orgmap/
├── assets/
├── controllers/
├── helpers/
├── messages/
│   ├── de/
│   └── en/
├── migrations/
├── models/
├── permissions/
├── resources/
│   ├── css/
│   └── js/
├── views/
├── widgets/
├── CHANGELOG.md
├── README.md
├── Events.php
├── Module.php
├── config.php
└── module.json
```

------------------------------------------------------------------------

# Database

Core tables:

-   orgmap_node
-   orgmap_connection
-   orgmap_asset
-   orgmap_organ

------------------------------------------------------------------------

# Technologies

-   HumHub
-   Yii2
-   Bootstrap 5
-   SVG
-   JavaScript
-   Font Awesome

------------------------------------------------------------------------

# Development Notes

-   Keep controllers lightweight.
-   Reuse helper classes.
-   Use ActionButtonHelper for recurring actions.
-   Use Yii::t() for all visible texts.
-   Keep JavaScript modular.
-   Prefer reusable CSS components.

------------------------------------------------------------------------

# Roadmap

Planned features:

-   Import / Export
-   Automatic layouts
-   Mini map
-   More node types
-   More connection styles
-   Additional language packs

------------------------------------------------------------------------

# Changelog

See **CHANGELOG.md** for release history.

The current release was verified with HumHub 1.19.3 on PHP 8.3.30 / MySQL
8.0.44 with local file storage. Codeception passed 65 tests with 356 assertions,
plus the module browser test. HumHub 1.20 icon compatibility follows the official
HumHub icon renderer and icon picker APIs; a local 1.20 runtime was not available
for this release.
See [installation, upgrade and backup instructions](INSTALLATION-UPGRADE.md)
for the package scope and deployment precautions. Other runtime combinations
require separate verification; compatibility with HumHub 2.x is not claimed.

------------------------------------------------------------------------

# Support

ORGMAP is actively developed.

Contributions, feature requests and bug reports are welcome.

## Veröffentlichung 1.4.9

Das geprüfte Installations-ZIP steht unter den GitHub-Releases bereit.
[Prüfstand und verbleibende Grenzen](docs/TESTING-1.4.9.md).



### Hilfe bei Hintergrund und Position (ab 1.4.8)

Das Bearbeitungsformular warnt, wenn ein ausgewähltes Bild wegen «Nur Farbe»
oder «Kein Hintergrund» verborgen bleibt. «Ausgewähltes Bild anzeigen» schaltet
auf Bilddarstellung um; gespeichert wird erst mit «Speichern».
Bei Elementen ausserhalb der Karte erscheint ein Positionshinweis.
«Element in der Karte zentrieren» verschiebt nur das bearbeitete Element.
«Hintergrund auf Kartengrösse setzen» ändert dessen Grösse und Mittelpunkt,
aber keine anderen Elemente. Zum gemeinsamen Einpassen der Anordnung zuerst
speichern und danach in den Karteneinstellungen die Arbeitsfläche an den
Hintergrund anpassen.
