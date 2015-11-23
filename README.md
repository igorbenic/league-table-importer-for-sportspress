# League Table Importer for SportsPress 

 

Import league tables for SportsPress and add non existing teams to WordPress.

**How to extend with new importers?**
This can be done by contributing to this plugin or by creating your own importer in a separate plugin. A simple example can be seen in the includes/class-options-excel.php

## Description 

This plugin is used to import league tables into SportsPress League Tables. Teams that do not exist are created automatically.

You can select an existing league table to update, equalize the imported teams with the existing teams or the team will be created automatically if selected.
Every configuration setting can be select for each column that is imported so that it is flexible enough to import excels that are not always the same in columns.

At the moment this plugin supports only Excel 2007 and up. Excels in older versions are not tested.

For now, after each new file upload the old file is deleted from the server.

Roadmap:

 - Refactor and support new formats
 - Better UI 
 - List of uploaded files + delete option
 

## Installation  


1. Upload this plugin folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

## Frequently Asked Questions 

### Will there be other file formats supported?  

If other are interested in using this plugin I can look at how to import and support other file formats. At the moment, there is a request for XML / HTML and this is under development.


## Changelog 

**1.0**
* Code refactored. Easily extendable with new importers

**0.3.1**
* Added example table

**0.3**
* Fixed the bug when uploading a file with the same name as the previous one. That file would be deleted also. Not anymore.

**0.2**

* Fixed Array Dereferencing to support PHP < 5.4

**0.1**

* Initial upload
