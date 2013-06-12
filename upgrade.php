<?php
/***************************************************************************
 *
 *   Usermap-system for MyBB
 *   Copyright: © 2008-2013 Online - Urbanus
 *   
 *   Website: http://www.Online-Urbanus.be
 *   
 *   Last modified: 11/06/2013 by Paretje
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is based on the GPLed mod called "skunkmap" version 1.1,
 *   made by King Butter - NCAAbbs SkunkWorks Team
 *   <http://www.ncaabbs.com>, which was released on the MyBB Mods site on
 *   22nd May 2007 <http://mods.mybboard.net/view/skunkmap>.
 * 
 *   So, this way, I wish to credit the original developer for their
 *   indirect contribution to this work.
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

// Updated templates
$new_templates['usermap'] = "<html>
<head>
<title>{\$mybb->settings['bbname']} - {\$lang->usermap}</title>
{\$headerinclude}
<script type=\"text/javascript\" src=\"http://maps.googleapis.com/maps/api/js?key={\$mybb->settings['usermap_apikey']}&sensor=false\"></script>
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
	map = new google.maps.Map(document.getElementById(\"map\"), {
		center: new google.maps.LatLng({\$default_place['lat']}, {\$default_place['lon']}),
		zoom: {\$default_place['zoom']},
		mapTypeId: google.maps.MapTypeId.ROADMAP
	});
	setPins(map);
}
google.maps.event.addDomListener(window, 'load', initialize);
</script>
</head>
<body>
{\$header}
<table border=\"0\" cellspacing=\"{\$theme['borderwidth']}\" cellpadding=\"{\$theme['tablespace']}\" class=\"tborder\">
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
<center><div id=\"map\" style=\"width: {\$mybb->settings['usermap_width']}px; height: {\$mybb->settings['usermap_height']}px\"></div></center>
</td>
</tr>
</table>
{\$footer}
</body>
</html>";

$new_templates['usermap_pin'] = "<html>
<head>
<title>{\$mybb->settings['bbname']} - {\$lang->usermap}</title>
{\$headerinclude}
<script type=\"text/javascript\" src=\"http://maps.googleapis.com/maps/api/js?key={\$mybb->settings['usermap_apikey']}&sensor=false\"></script>
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
	map = new google.maps.Map(document.getElementById(\"map\"), {
		center: new google.maps.LatLng({\$coordinates}),
		zoom: 14,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	});
	setPins(map);
}
google.maps.event.addDomListener(window, 'load', initialize);</script>
</head>
<body>
{\$header}
<table border=\"0\" cellspacing=\"{\$theme['borderwidth']}\" cellpadding=\"{\$theme['tablespace']}\" class=\"tborder\">
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
<input type=\"text\" class=\"textbox\" size=\"40\" maxlength=\"255\" name=\"adress\" value=\"{\$mybb->input['adress']}\" />
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
<img name=\"pin_image\" src=\"images/pinimgs/{\$mybb->input['pinimg']}\">
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
<input type=\"hidden\" name=\"lat\" value=\"{\$users['usermap_lat']}\" />
<input type=\"hidden\" name=\"lon\" value=\"{\$users['usermap_lon']}\" />
<input type=\"hidden\" name=\"pinimg\" value=\"{\$mybb->input['pinimg']}\" />
<input type=\"hidden\" name=\"adress\" value=\"{\$mybb->input['adress']}\" />
<center><input type=\"submit\" class=\"submit\" value=\"{\$lang->ok}\"></center>
</form>
</td>
</tr>
<tr>
<td colspan=\"2\" class=\"trow1\">
<center><div id=\"map\" style=\"width: {\$mybb->settings['usermap_width']}px; height: {\$mybb->settings['usermap_height']}px\"></div></center>
</td>
</tr>
</table>
{\$footer}
</body>
</html>";

$new_templates['usermap_pinimgs_java'] = "<script type=\"text/javascript\">
var shadow = {
	url: \"images/pinimgs/shadow.png\",
	size: new google.maps.Size(22, 20),
	anchor: new google.maps.Point(6, 20)
}
{\$usermap_pinimgs_java_bit}
</script>";

$new_templates['usermap_pinimgs_java_bit'] = "var icon{\$file[0]} = {
	url: \"images/pinimgs/{\$pinimg['file']}\",
	size: new google.maps.Size(12, 20),
	anchor: new google.maps.Point(6, 20),
};";

$new_templates['usermap_pins_bit'] = "	var marker{\$count} = new google.maps.Marker({
		position: new google.maps.LatLng({\$coordinates}),
		icon: icon{\$userpin['pinimg']},
		shadow: shadow
	});
	marker{\$count}.setMap(map);
	google.maps.event.addListener(marker{\$count}, \"click\", function()
	{
		new google.maps.InfoWindow({content: \"{\$userpin['window']}\"}).open(map, marker{\$count});
	});";

$new_templates['usermap_places_java_bit'] = "		case '{\$places['pid']}':
			map.setCenter(new google.maps.LatLng({\$places['lat']}, {\$places['lon']}));
			map.setZoom({\$places['zoom']});
		break;";

// Update the new template into the database
foreach($new_templates as $title => $template)
{
	$template_update = array(
		'template'	=> $db->escape_string($template),
		'version'	=> $db->escape_string($mybb->version_code),
		'dateline'	=> TIME_NOW
	);
	$db->update_query("templates", $template_update, "title='" .
		$db->escape_string($title) . "' AND sid='-1'");
}

echo "Your Usermap version is succesfully upgraded!";
?>
