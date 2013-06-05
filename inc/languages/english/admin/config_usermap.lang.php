<?php
/***************************************************************************
 *
 *   Usermap-system for MyBB
 *   Copyright: © 2008 Online - Urbanus
 *   
 *   Website: http://www.Online-Urbanus.be
 *   
 *   Last modified: 21/09/2008 by Paretje
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is based on the GPLed mod called "skunkmap" version 1.1, made by King Butter - NCAAbbs SkunkWorks Team <http://www.ncaabbs.com>, which was released on the MyBB Mods site on 22nd May 2007 <http://mods.mybboard.net/view/skunkmap>.
 * 
 * So, I call my special thanks to the maker(s) of that program!
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 ***************************************************************************/

$l['usermap'] = "Usermap";
$l['save'] = "Save";

$l['can_manage_usermap'] = "Can Manage Usermap";

$l['can_view_usermap'] = "Can view the Usermap?";
$l['can_add_usermap_pin'] = "Can add a pin on the Usermap?";

$l['admin_log_config_usermap_add_place'] = "Added Usermap-place #{1} ({2})";
$l['admin_log_config_usermap_edit_place'] = "Edited Usermap-place #{1} ({2})";
$l['admin_log_config_usermap_delete_place'] = "Deleted Usermap-place #{1} ({2})";
$l['admin_log_config_usermap_order_places'] = "Ordered Usermap-places";

$l['admin_log_config_usermap_add_pinimg'] = "Added pin-image #{1} ({2})";
$l['admin_log_config_usermap_edit_pinimg'] = "Edited pin-image #{1} ({2})";
$l['admin_log_config_usermap_delete_pinimg'] = "Deleted pin-image #{1} ({2})";

$l['nav_manage_places'] = "Manage Places";
$l['nav_add_place'] = "Add Place";
$l['nav_edit_place'] = "Edit Place";
$l['nav_manage_pinimgs'] = "Manage Pin-Images";
$l['nav_add_pinimg'] = "Add Pin-Image";
$l['nav_edit_pinimg'] = "Edit Pin-Image";

$l['nav_manage_places_desc'] = "Here you can manage the Usermap places on your board.";
$l['nav_add_place_desc'] = "Here you can add a Usermap place.";
$l['nav_edit_place_desc'] = "Here you can edit a Usermap place.";
$l['nav_manage_pinimgs_desc'] = "Here you can manage the Usermap pin-images on your board.";
$l['nav_add_pinimg_desc'] = "Here you can add a Usermap pin-image.";
$l['nav_edit_pinimg_desc'] = "Here you can edit a Usermap pin-image.";

$l['places'] = "Places";
$l['order'] = "Order";
$l['save_order'] = "Save Order";
$l['no_places'] = "There are no places.";

$l['place_name'] = "Place name";
$l['place_lat'] = "Latitude";
$l['place_lon'] = "Longitude";
$l['place_zoom'] = "Zoom";
$l['place_displayorder'] = "Displayorder";
$l['place_default'] = "Default place";

$l['error_missing_name'] = "You didn't enter a name for this place";
$l['error_missing_lat'] = "You didn't enter the latitude for this place";
$l['error_missing_lon'] = "You didn't enter the longitude for this place";
$l['error_missing_zoom'] = "You didn't enter the zoom-level for this place";
$l['error_missing_displayorder'] = "You didn't enter the displayorder for this place";
$l['error_missing_default'] = "You didn't select if you want to make this place as the default one";
$l['error_missing_order'] = "You didn't enter any order information";
$l['placedoesntexist'] = "The selected place doesn't exist.";

$l['delete_place_confirmation'] = "Are you sure you want to delete this place?";

$l['added_place'] = "The place is successfully added.";
$l['edited_place'] = "The place is successfully edited.";
$l['deleted_place'] = "The place is successfully deleted.";
$l['ordered_places'] = "The displayorder of the places is successfylly changed.";

$l['pinimgs'] = "Pin-Images";
$l['no_pinimgs'] = "There are no pin-images.";

$l['pinimg_name'] = "Pin-image name";
$l['pinimg_file'] = "Pin-image";
$l['pinimg_default'] = "Default pin-image";

$l['error_missing_pinimg_name'] = "You didn't enter a name for this pin-image";
$l['error_missing_pinimg_file'] = "You didn't upload a image to use as pin-image.";
$l['error_missing_pinimg_default'] = "You didn't select if you want to make this pin-images as the default one";
$l['not_writable'] = "The follow directory has no chmod 777:<br />\n{1}";
$l['error_uploadfailed'] = "There is an error with the upload of the image.";
$l['pinimgdoesntexist'] = "The selected pin-image doesn't exist.";

$l['delete_pinimg_confirmation'] = "Are you sure you want to delete this pin-image?";

$l['added_pinimg'] = "The pin-image is successfully added.";
$l['edited_pinimg'] = "The pin-image is successfully edited.";
$l['deleted_pinimg'] = "The pin-image is successfully deleted.";
?>