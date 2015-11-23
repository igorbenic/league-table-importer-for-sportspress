=== League Table Importer for SportsPress ===
Contributors: ibenic
Tags: sportspress, import
Requires at least: 4.0
Tested up to: 4.0
Stable tag: 1.0
License: GPLv2 
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Import league tables for SportsPress and add non existing teams to WordPress.

== Description ==

This plugin is used to import league tables into SportsPress League Tables. Teams that do not exist are created automatically.

You can select an existing league table to update, equalize the imported teams with the existing teams or the team will be created automatically if selected.
Every configuration setting can be select for each column that is imported so that it is flexible enough to import excels that are not always the same in columns.

At the moment this plugin supports only Excel 2007 and up. Excels in older versions are not tested.

For now, after each new file upload the old file is deleted from the server.

Roadmap:

 - Refactor and support new formats
 - Better UI 
 - List of uploaded files + delete option

GitHub Repository: https://github.com/igorbenic/league-table-importer-for-sportspress

== Installation ==


1. Upload this plugin folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Will there be other file formats supported? =

If other are interested in using this plugin I can look at how to import and support other file formats. At the moment, there is a request for XML / HTML and this is under development.


== Changelog ==

= 1.0 =
* Code refactored. Easily extendable with new importers

= 0.3.1 =
* Added Example Table

= 0.3 =
* Fixed bug that removed the uploaded file if the file had the same name as the previous uploaded one.

= 0.2 =
* Fixed Array Dereferencing to support PHP < 5.4

= 0.1 =
* Initial upload