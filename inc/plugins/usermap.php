<?php
/***************************************************************************
 *
 *   Usermap-system for MyBB
 *   Copyright: © 2008-2010 Online - Urbanus
 *   
 *   Website: http://www.Online-Urbanus.be
 *   
 *   Last modified: 15/04/2010 by Paretje
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
 
if(!defined("IN_MYBB"))
{
	die("This file cannot be accessed directly.");
}

//Plugins
$plugins->add_hook("global_start", "usermap_global");
$plugins->add_hook("fetch_wol_activity_end", "usermap_online_activity");
$plugins->add_hook("build_friendly_wol_location_end", "usermap_online_location");
$plugins->add_hook("admin_config_menu", "usermap_admin_config_menu");
$plugins->add_hook("admin_config_action_handler", "usermap_admin_config_action_handler");
$plugins->add_hook("admin_config_permissions", "usermap_admin_config_permissions");
$plugins->add_hook("admin_user_groups_edit", "usermap_admin_user_groups_edit");
$plugins->add_hook("admin_user_groups_edit_commit", "usermap_admin_user_groups_edit_commit");
$plugins->add_hook("admin_tools_adminlog_start", "usermap_tools_adminlog");

function usermap_info()
{
	return array(
		"name"		=> "Usermap",
		"description"	=> "Makes a usermap system.",
		"website"	=> "http://www.Online-Urbanus.be",
		"author"	=> "Paretje",
		"authorsite"	=> "http://www.Online-Urbanus.be",
		"version"	=> "1.1.2",
		"guid"		=> "68b7d024b9cefc58cd2c8676a0ae60f8",
		"compatibility" => "14*,16*"
	);
}

function usermap_install()
{
	global $cache, $db;
	
	//Insert Usermap tables
	$db->write_query("CREATE TABLE `".TABLE_PREFIX."usermap_places` (
	`pid` INT(5) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(120) NOT NULL,
	`lat` FLOAT NOT NULL,
	`lon` FLOAT NOT NULL,
	`zoom` INT(2) NOT NULL,
	`displayorder` INT(5) NOT NULL,
	PRIMARY KEY (`pid`)
	) TYPE=MyISAM".$db->build_create_table_collation().";");
	
	//Insert
	$place1 = array(
		"name"		=> "World",
		"lat"		=> "31.353637",
		"lon"		=> "-1.054687",
		"zoom"		=> "2",
		"displayorder"	=> "1"
	);
	$place2 = array(
		"name"		=> "Europe",
		"lat"		=> "49.439557",
		"lon"		=> "11.513672",
		"zoom"		=> "4",
		"displayorder"	=> "2"
	);
	$place3 = array(
		"name"		=> "USA",
		"lat"		=> "37.0625",
		"lon"		=> "-95.677068",
		"zoom"		=> "4",
		"displayorder"	=> "3"
	);
	
	$db->insert_query("usermap_places", $place1);
	$db->insert_query("usermap_places", $place2);
	$db->insert_query("usermap_places", $place3);
	
	//Insert datacache information
	//Pinimgs
	$pinimgs[] = array(
		"name"		=> "Default",
		"file"		=> "pin.png"
	);
	
	$cache->update("usermap_pinimgs", $pinimgs);
	
	//Default "settings"
	$defaults = array(
		"place"		=> "1",
		"pinimg"	=> "0"
	);
	
	$cache->update("usermap", $defaults);
	
	//Settings
	$setting_group = array(
		"name"			=> "usermap",
		"title"			=> "Usermap Options",
		"description"		=> "This section contains the settings to configure the Usermap-system.",
		"disporder"		=> "25",
		"isdefault"		=> "0"
	);
	$gid = $db->insert_query("settinggroups", $setting_group);
	
	$setting1 = array(
		"name"			=> "usermap_apikey",
		"title"			=> "API-key",
		"description"		=> "Your API-key for Google Maps.",
		"optionscode"		=> "text",
		"value"			=> "",
		"disporder"		=> "1",
		"gid"			=> $gid
	);
	$setting2 = array(
		"name"			=> "usermap_width",
		"title"			=> "Map Width",
		"description"		=> "The width of the map.",
		"optionscode"		=> "text",
		"value"			=> "750",
		"disporder"		=> "2",
		"gid"			=> $gid
	);
	$setting3 = array(
		"name"			=> "usermap_height",
		"title"			=> "Map Height",
		"description"		=> "The height of the map.",
		"optionscode"		=> "text",
		"value"			=> "450",
		"disporder"		=> "3",
		"gid"			=> $gid
	);
	
	$db->insert_query("settings", $setting1);
	$db->insert_query("settings", $setting2);
	$db->insert_query("settings", $setting3);
	
	rebuild_settings();
	
	//Mybb-tables
	$db->write_query("ALTER TABLE `".TABLE_PREFIX."usergroups` ADD `canviewusermap` INT(1) NOT NULL DEFAULT '1',
	ADD `canaddusermappin` INT(1) NOT NULL DEFAULT '1';");
	
	$db->write_query("ALTER TABLE `".TABLE_PREFIX."users` ADD `usermap_lat` FLOAT NOT NULL,
	ADD `usermap_lon` FLOAT NOT NULL,
	ADD `usermap_pinimg` VARCHAR(255) NOT NULL,
	ADD `usermap_adress` VARCHAR(255) NOT NULL;");
	
	$db->write_query("UPDATE ".TABLE_PREFIX."usergroups SET canviewusermap='0', canaddusermappin='0' WHERE gid='7'");
	
	//Update usergroupschache
	$cache->update_usergroups();
	
	//Update adminpermissions
	change_admin_permission("config", "usermap", 1);
	
	//Templates
	$templates['usermap'] = "<html>
<head>
<title>{\$mybb->settings[\'bbname\']} - {\$lang->usermap}</title>
{\$headerinclude}
<script type=\"text/javascript\" src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;key={\$mybb->settings[\'usermap_apikey\']}\"></script>
<script type=\"text/javascript\">
var map = true;
</script>
{\$usermap_pinimgs_swapimg}
{\$usermap_pinimgs_java}
{\$usermap_pins}
{\$usermap_places_java}
<script type=\"text/javascript\">
function initialize()
{
	map = new GMap2(document.getElementById(\"map\"));
	map.setCenter(new GLatLng({\$default_place[\'lat\']}, {\$default_place[\'lon\']}), {\$default_place[\'zoom\']});
	map.setUIToDefault();
	setPins(map);
}
</script>
</head>
<body  onload=\"initialize()\" onunload=\"GUnload()\">
{\$header}
<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\">
<tr>
<td colspan=\"2\" class=\"thead\"><strong>{\$lang->usermap}</strong></td>
</tr>
{\$usermap_form}
<tr>
<td class=\"trow2\">
<strong>{\$lang->place}</strong>
</td>
<td class=\"trow2\">
<select name=\"place\" id=\"place\" onchange=\"moveMap(this.value)\">
{\$usermap_places_bit}
</select>
</td>
</tr>
<tr>
<td colspan=\"2\" class=\"trow1\">
<center><div id=\"map\" style=\"width: {\$mybb->settings[\'usermap_width\']}px; height: {\$mybb->settings[\'usermap_height\']}px\"></div></center>
</td>
</tr>
</table>
{\$footer}
</body>
</html>";
	
	$templates['usermap_form'] = "<form method=\"post\" action=\"usermap.php\">
<input type=\"hidden\" name=\"action\" value=\"lookup\" />
<tr>
<td class=\"trow1\" width=\"40%\">
<strong>{\$lang->yourpin}</strong>
</td>
<td class=\"trow1\">
<input type=\"text\" class=\"textbox\" size=\"40\" maxlength=\"255\" name=\"adress\" value=\"{\$mybb->user[\'usermap_adress\']}\" />
</td>
</tr>
<tr>
<td class=\"trow2\" width=\"40%\">
<strong>{\$lang->yourpinimg}</strong>
</td>
<td class=\"trow2\">
<select name=\"pinimg\" onchange=\"swapIMG(this.value)\">
{\$usermap_pinimgs_bit}
</select>
<img name=\"pin_image\" src=\"images/pinimgs/{\$default_pinimg[\'file\']}\" alt=\"\" />
</td>
</tr>
<tr>
<td colspan=\"2\" class=\"trow1\">
<center><input type=\"submit\" class=\"submit\" value=\"{\$lang->lookup}\" /></center>
</td>
</tr>
</form>";
	
	$templates['usermap_pin'] = "<html>
<head>
<title>{\$mybb->settings[\'bbname\']} - {\$lang->usermap}</title>
{\$headerinclude}
<script type=\"text/javascript\" src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;key={\$mybb->settings[\'usermap_apikey\']}\"></script>
{\$usermap_pinimgs_swapimg}
{\$usermap_pinimgs_java}
{\$usermap_pins}
{\$usermap_places_java}
<script type=\"text/javascript\">
function initialize()
{
	var map = new GMap2(document.getElementById(\"map\"));
	map.setCenter(new GLatLng({\$coordinates}), 14);
	map.setUIToDefault();
	setPins(map);
}
</script>
</head>
<body  onload=\"initialize()\" onunload=\"GUnload()\">
{\$header}
<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\">
<tr>
<td colspan=\"2\" class=\"thead\"><strong>{\$lang->usermap}</strong></td>
</tr>
<form method=\"post\" action=\"usermap.php\">
<input type=\"hidden\" name=\"action\" value=\"lookup\" />
<tr>
<td class=\"trow1\" width=\"40%\">
<strong>{\$lang->yourpin}</strong>
</td>
<td class=\"trow1\">
<input type=\"text\" class=\"textbox\" size=\"40\" maxlength=\"255\" name=\"adress\" value=\"{\$mybb->input[\'adress\']}\" />
</td>
</tr>
<tr>
<td class=\"trow2\" width=\"40%\">
<strong>{\$lang->yourpinimg}</strong>
</td>
<td class=\"trow2\">
<select name=\"pinimg\" onchange=\"swapIMG(this.value)\">
{\$usermap_pinimgs_bit}
</select>
<img name=\"pin_image\" src=\"images/pinimgs/{\$mybb->input[\'pinimg\']}\">
</td>
</tr>
<tr>
<td colspan=\"2\" class=\"trow1\">
<center><input type=\"submit\" class=\"submit\" value=\"{\$lang->lookup}\"></center>
</td>
</tr>
</form>
<tr>
<td colspan=\"2\" class=\"trow2\">
<form method=\"post\" action=\"usermap.php\">
<input type=\"hidden\" name=\"action\" value=\"do_pin\" />
<input type=\"hidden\" name=\"lat\" value=\"{\$users[\'usermap_lat\']}\" />
<input type=\"hidden\" name=\"lon\" value=\"{\$users[\'usermap_lon\']}\" />
<input type=\"hidden\" name=\"pinimg\" value=\"{\$mybb->input[\'pinimg\']}\" />
<input type=\"hidden\" name=\"adress\" value=\"{\$mybb->input[\'adress\']}\" />
<center><input type=\"submit\" class=\"submit\" value=\"{\$lang->ok}\"></center>
</form>
</td>
</tr>
<tr>
<td colspan=\"2\" class=\"trow1\">
<center><div id=\"map\" style=\"width: {\$mybb->settings[\'usermap_width\']}px; height: {\$mybb->settings[\'usermap_height\']}px\"></div></center>
</td>
</tr>
</table>
{\$footer}
</body>
</html>";
	
	$templates['usermap_pinimgs_bit'] = "<option value=\"{\$pinimg[\'file\']}\"{\$selected_pinimg[\$pinimg[\'file\']]}>{\$pinimg[\'name\']}</option>";
	
	$templates['usermap_pinimgs_java'] = "<script type=\"text/javascript\">
{\$usermap_pinimgs_java_bit}
</script>";
	
	$templates['usermap_pinimgs_java_bit'] = "var icon{\$file[0]} = new GIcon();
icon{\$file[0]}.image = \"images/pinimgs/{\$pinimg[\'file\']}\";
icon{\$file[0]}.shadow = \"images/pinimgs/shadow.png\";
icon{\$file[0]}.iconSize = new GSize(12, 20);
icon{\$file[0]}.shadowSize = new GSize(22, 20);
icon{\$file[0]}.iconAnchor = new GPoint(6, 20);
icon{\$file[0]}.infoWindowAnchor = new GPoint(5, 1);";
	
	$templates['usermap_pinimgs_swapimg'] = "<script type=\"text/javascript\">
function swapIMG(imgname)
{
	document.images[\'pin_image\'].src = \"images/pinimgs/\"+imgname;
}
</script>";
	
	$templates['usermap_pins'] = "<script type=\"text/javascript\">
function setPins(map)
{
	{\$usermap_pins_bit}
}
</script>";
	
	$templates['usermap_pins_bit'] = "	var marker{\$count} = new GMarker(new GLatLng({\$coordinates}), icon{\$userpin[\'pinimg\']});
	map.addOverlay(marker{\$count});
	GEvent.addListener(marker{\$count}, \"mouseover\", function()
	{
		marker{\$count}.openInfoWindowHtml(\"{\$userpin[\'window\']}\");
	})";
	
	$templates['usermap_pins_bit_user'] = "{\$username}{\$avatar}";
	
	$templates['usermap_places_bit'] = "<option value=\"{\$places[\'pid\']}\"{\$selected_place[\$places[\'pid\']]}>{\$places[\'name\']}</option>";
	
	$templates['usermap_places_java'] = "<script type=\"text/javascript\">
function moveMap(country)
{
	switch(country)
	{
		{\$usermap_places_java_bit}
	}
}
</script>";
	
	$templates['usermap_places_java_bit'] = "		case \'{\$places[\'pid\']}\':
			map.setCenter(new GLatLng({\$places[\'lat\']}, {\$places[\'lon\']}), {\$places[\'zoom\']});
		break;";
	
	// Insert the new templates into the database.
	foreach($templates as $title => $template)
	{
		$template_insert = array(
			"title"		=> $title,
			"template"	=> $template,
			"sid"		=> "-1",
			'version'	=> "1400",
			'dateline'	=> TIME_NOW
		);
		
		$db->insert_query("templates", $template_insert);
	}
}

function usermap_uninstall()
{
	global $db, $cache;
	
	//Delete Usermap tables
	$db->write_query("DROP TABLE `".TABLE_PREFIX."usermap_places`;");
	
	//Delete datacache
	$cache->update("usermap_pinimgs", false);
	$cache->update("usermap", false);
	
	//MyBB-tables
	$db->write_query("ALTER TABLE `".TABLE_PREFIX."usergroups` DROP `canviewusermap`,
	DROP `canaddusermappin`;");
	
	$db->write_query("ALTER TABLE `".TABLE_PREFIX."users` DROP `usermap_lat`,
	DROP `usermap_lon`,
	DROP `usermap_pinimg`,
	DROP `usermap_adress`;");
	
	//Update usergroupschache
	$cache->update_usergroups();
	
	//Delete MyBB settings
	$query = $db->query("SELECT * FROM ".TABLE_PREFIX."settinggroups WHERE name='usermap'");
	$setting_group = $db->fetch_array($query);
	
	$db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE gid='".$setting_group['gid']."'");
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE gid='".$setting_group['gid']."'");
	
	rebuild_settings();
	
	//Delete templates
	$deletetemplates = array('usermap', 'usermap_form', 'usermap_pin', 'usermap_pinimgs', 'usermap_pinimgs_bit', 'usermap_pinimgs_java', 'usermap_pinimgs_java_bit', 'usermap_pinimgs_swapimg', 'usermap_pins', 'usermap_pins_bit', 'usermap_pinimgs_bit_user', 'usermap_places_bit', 'usermap_places_java', 'usermap_places_java_bit');
	
	foreach($deletetemplates as $title)
	{
		$db->delete_query("templates", "title='".$title."'");
	}
}

function usermap_activate()
{
	//Update MyBB templates
	require_once MYBB_ROOT."inc/adminfunctions_templates.php";

	find_replace_templatesets("footer", "#".preg_quote("{\$lang->bottomlinks_syndication}</a>")."#", "{\$lang->bottomlinks_syndication}</a> | <a href=\"{\$mybb->settings['bburl']}/usermap.php\">{\$lang->usermap}</a>");
}

function usermap_deactivate()
{
	//Revert MyBB templates
	require_once MYBB_ROOT."inc/adminfunctions_templates.php";

	find_replace_templatesets("footer", "#".preg_quote(" | <a href=\"{\$mybb->settings['bburl']}/usermap.php\">{\$lang->usermap}</a>")."#", "", 0);
}

function usermap_is_installed()
{
	global $db;
	
	if($db->table_exists("usermap_places"))
	{
		return true;
	}
	
	return false;
}

function usermap_global()
{
	global $lang;
	
	$lang->load("usermap");
}

function usermap_online_activity($user_activity)
{
	//Get the filename
	$split_loc = explode(".php", $user_activity['location']);
	$filename = my_substr($split_loc[0], -my_strpos(strrev($split_loc[0]), "/"));
	
	if($user_activity['activity'] == "unknown" && $filename == "usermap")
	{
		$user_activity['activity'] = "usermap";
		
		return $user_activity;
	}
}

function usermap_online_location($plugin_array)
{
	global $lang;
	
	if($plugin_array['user_activity']['activity'] == "usermap")
	{
		$plugin_array['location_name'] = $lang->viewing_usermap;
		
		return $plugin_array;
	}
}

function usermap_admin_config_menu($sub_menu)
{
	global $lang;
	
	//Load the language files
	$lang->load("config_usermap");
	
	//Add the menu item
	$sub_menu[] = array("id" => "usermap", "title" => $lang->usermap, "link" => "index.php?module=config/usermap");
	
	return $sub_menu;
}

function usermap_admin_config_action_handler($actions)
{
	$actions['usermap'] = array('active' => 'usermap', 'file' => 'usermap.php');
	
	return $actions;
}

function usermap_admin_config_permissions($admin_permissions)
{
	global $lang;
	
	//Load the language files
	$lang->load("config_usermap");
	
	//Add the permission item
	$admin_permissions['usermap'] = $lang->can_manage_usermap;
	
	return $admin_permissions;
}

function usermap_admin_user_groups_edit()
{
	global $plugins;
	
	$plugins->add_hook("admin_formcontainer_end", "usermap_admin_user_groups_edit_graph");
}

function usermap_admin_user_groups_edit_graph()
{
	global $form_container, $lang, $form, $mybb;
	echo $form_container->_title;
	//Check if it's the misc tab generating now
	if($form_container->_title == $lang->misc)
	{
		//Load the language files
		$lang->load("config_usermap");
		
		//Add the Usermap options
		$usermap_options = array(
			$form->generate_check_box("canviewusermap", 1, $lang->can_view_usermap, array("checked" => $mybb->input['canviewusermap'])),
			$form->generate_check_box("canaddusermappin", 1, $lang->can_add_usermap_pin, array("checked" => $mybb->input['canaddusermappin']))
		);
		$form_container->output_row($lang->usermap, "", "<div class=\"group_settings_bit\">".implode("</div><div class=\"group_settings_bit\">", $usermap_options)."</div>");
	}
}

function usermap_admin_user_groups_edit_commit()
{
	global $updated_group, $mybb;
	
	$updated_group['canviewusermap'] = $mybb->input['canviewusermap'];
	$updated_group['canaddusermappin'] = $mybb->input['canaddusermappin'];
}

function usermap_tools_adminlog()
{
	global $lang;
	
	$lang->load("config_usermap");
}
?>
