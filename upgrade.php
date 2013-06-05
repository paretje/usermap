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

//Define MyBB and includes
define("IN_MYBB", 1);

require_once "./global.php";

//Updated template
$new_templates['usermap_pin'] = "<html>
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

//Update the new template into the database.
$template_update = array(
	"template"	=> $new_templates['usermap_pin'],
	'version'	=> "1412",
	'dateline'	=> TIME_NOW
);

$db->update_query("templates", $template_update, "title='usermap_pin' AND sid='-1'");

echo "Your Usermap version is succesfully upgraded!";
?>
