=== PS Last Updated Admin Columns ===
Contributors: PerS
Tags: admin columns, posts, pages, last updated, modified date
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a sortable Last Updated column to public post type admin lists.

== Description ==

PS Last Updated Admin Columns adds a sortable Last Updated column to public post type list tables in the WordPress admin.

The column:

* Appears directly after the core Date column.
* Uses the same date and time format as the core Date column.
* Shows the last editor on row hover.
* Links the editor's name to their WordPress profile when permitted.
* Falls back to the post author when no last editor is recorded.
* Includes Norwegian Bokmål translations.

== Installation ==

1. Upload the `ps-last-updated` directory to `/wp-content/plugins/`, or install it through the Plugins screen.
2. Activate the plugin through the Plugins screen in WordPress.
3. Open a public post type list in the WordPress admin.

== Frequently Asked Questions ==

= Which post types are supported? =

The column is added to public post types that have a WordPress admin list table. Media attachments are excluded.

= Is the column sortable? =

Yes. Click the Last Updated column heading to sort by the post modified date.

== Changelog ==

= 1.0.0 =

* Added a sortable Last Updated column after the Date column.
* Added core-compatible date and time formatting.
* Added last-editor information on row hover with a profile link.
* Added Norwegian Bokmål translations.
