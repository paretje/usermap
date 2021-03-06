IMPORTANT NOTE
==============
This plugin is no longer maintained here or by Paretje. Starting from MyBB 1.8, development has been taken over by Jockl. It's new home is [mybb.de](https://www.mybb.de/erweiterungen/18x/plugins-neueseiten/usermap2).

Usermap 1.2 beta
================
Online - Urbanus <info@Online-Urbanus.be>
http://www.Online-Urbanus.be

This program is released under the GNU General Public License (GPL) version 3,
see the file 'COPYING' for more information.

Description
===========
Usermap is a plugin for the MyBB bulletin board system which aims to add a map
to the system where users can pin their location on. To achieve this, Usermap
currently uses Google Maps. So, to use Usermap, you'll need a Google Maps API
key, and accept the terms of use of the Google Maps API.

Installation instructions
=========================
Installing the Usermap-plugin on your MyBB installation is quite easy:
    1. Upload the files, except upgrade.php, to the root of your MyBB
       installation
    2. Activate the plugin
    3. Get a Google Maps API key
    4. Add the key to the Usermap settings in the MyBB ACP

Upgrading from Usermap 1.1.2
============================
    1. Overwrite the existing Usermap-files with the new files
    2. Run upgrade.php
    3. Delete upgrade.php, as leaving it on your board is a security risk
    4. Get a new Google Maps API key
    5. Add the key to the Usermap settings in the MyBB ACP
