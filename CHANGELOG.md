# CHANGELOG

All notable changes to this project will be documented in this file.

The format is based on **Keep a Changelog** and follows **Semantic
Versioning** where practical.

------------------------------------------------------------------------

## \[1.4.0-beta.1\] - 2026-08-14

### Added

-   Image source option for nodes without a background image
-   Centered initial position for newly created nodes

### Changed

-   Default node opacity is now 100 percent
-   Responsive map positioning for mobile and different viewport sizes
-   Print layout, colors and connection visibility

### Fixed

-   SVG connections being clipped or covered after loading and editing
-   Incorrect map position before background images finished loading
-   Excessive empty space above the map on iPhone
-   Missing arrows and unreadable legend text in printed output

------------------------------------------------------------------------

## \[1.3.1\] - 2026-07-21

### Changed

-   Updated TopMenu registration to the HumHub 1.19 menu API
-   Replaced the deprecated array entry with `MenuLink` and `addEntry()`
-   Added the unique menu ID `topmenu-orgmap`
-   Updated active-state detection to use PHP nullsafe access

### Fixed

-   Dashboard startup failure caused by the removed `TopMenu::addItem()` method

------------------------------------------------------------------------

## \[1.3.0\] - 2026-07-19

### Added

-   Accessible landmarks, table captions and relationship descriptions
-   Keyboard operation for icon selection, tree groups and node resizing
-   Live announcements for search results and map zoom
-   Support for reduced motion and Windows high-contrast mode
-   Accessible form error summaries and scrollable regions

### Changed

-   Clear visual distinction between focus, selection and active views
-   Improved text size, subtitle contrast and dark-mode focus styles
-   Correct heading hierarchy and semantic controls throughout the module
-   New-tab links are announced before activation

### Fixed

-   Hidden node content caused by overriding screen-reader labels
-   Dead links appearing in the keyboard navigation order
-   Invalid interactive nesting in map edit mode
-   Missing image alternatives and form label associations
-   Invalid toolbar and map container markup

------------------------------------------------------------------------

## \[1.2.0\] - 2026-07-11

### Added

-   Fullscreen presentation mode
-   Landscape print and PDF output
-   Extended tree search with live result count
-   Keyboard navigation with arrow, Home and End keys
-   Accessible focus, active-state and group controls

### Fixed

-   Space fallback links for older nodes without an explicit link type
-   Active navigation state after keyboard, PJAX and browser navigation
-   Print clipping caused by transform-based scaling

------------------------------------------------------------------------

## \[1.1.0\] - 2026-07-11

### Changed

-   Hardened permissions, uploads, output encoding and JSON endpoints
-   Improved connection rendering, zoom behavior and live path updates
-   Reduced database queries in map, tree and connection rendering
-   Added model-level relation and hierarchy validation
-   Added cleanup and foreign-key migration for database integrity

------------------------------------------------------------------------

## \[1.0.0\] - 2026-06-30

### Added

-   Initial public release of ORGMAP
-   Interactive organization map
-   Drag & Drop positioning of nodes
-   Zoom and pan navigation
-   Tree, Map and Split views
-   Organization management
-   Asset management
-   Background nodes and panels
-   Connection editor
-   Multiple connection types
-   Custom connection labels
-   Connection arrows and line styles
-   Workspace configuration
-   Guest access support
-   Dark mode support
-   Responsive layout
-   Internationalization (i18n)
-   English and German translation structure
-   Centralized ActionButtonHelper
-   Unified administration interface
-   SVG-based connection rendering

### Changed

-   Unified administration buttons and styling
-   Standardized button sizes
-   Improved connection editing workflow
-   Improved workspace controls
-   Improved tree navigation
-   Improved mobile usability

### Fixed

-   Connection rendering issues
-   Connection label positioning
-   Tree navigation behaviour
-   Background rendering
-   Various UI consistency improvements
-   General bug fixes and stability improvements
