<?php
/***************************************************************************
 *
 *   Usermap-system for MyBB
 *   Copyright: © 2008-2013 Online - Urbanus
 *   
 *   Website: http://www.Online-Urbanus.be
 *   
 *   Last modified: 13/06/2013 by Paretje
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is based on the GPLed mod called "skunkmap" version 1.1,
 *   made by King Butter - NCAAbbs SkunkWorks Team
 *   <http://www.ncaabbs.com>, which was released on the MyBB Mods site on
 *   22nd May 2007 <http://mods.mybboard.net/view/skunkmap>.
 * 
 *   So, this way, I wish to credit the original developers for their
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

// Define MyBB, needed templates and includes
define("IN_MYBB", 1);

$templatelist = "usermap,usermap_form,usermap_pin,";
$templatelist .= "usermap_pins,usermap_pins_bit,usermap_pins_bit_user,";
$templatelist .= "usermap_places_bit,usermap_places_java,";
$templatelist .= "usermap_places_java_bit";

require_once "./global.php";

// Plugin hook
$plugins->run_hooks("username_start");

// Define point in navigation
add_breadcrumb($lang->usermap, "usermap.php");

// Control if the user has permission to view the usermap
if($mybb->usergroup['canviewusermap'] != "1")
{
	error_no_permission();
}

switch($mybb->input['action'])
{
	default:
		// TODO: This should be put in the database
		// Load the default place of the Usermap
		$defaults = $cache->read("usermap");
		
		// Select the default place by default
		$selected_place[$defaults['place']] = " selected=\"selected\"";
		
		// Load the places in the database
		$query2 = $db->query("SELECT * FROM ".TABLE_PREFIX."usermap_places ORDER BY displayorder ASC");
		while($places = $db->fetch_array($query2))
		{
			if($places['pid'] == $defaults['place'])
			{
				$default_place = array(
					"lat"		=> $places['lat'],
					"lon"		=> $places['lon'],
					"zoom"		=> $places['zoom']
				);
			}
			
			eval("\$usermap_places_bit .= \"".$templates->get("usermap_places_bit")."\";");
			eval("\$usermap_places_java_bit .= \"".$templates->get("usermap_places_java_bit")."\";");
		}
		
		// Load the needed JavaScript
		eval("\$usermap_places_java = \"".$templates->get("usermap_places_java")."\";");
		
		// TODO: Any other interesting columns in the database?
		// Load the pin locations
		$query = $db->query("SELECT uid, username, usergroup, displaygroup, avatar, usermap_lat, usermap_lon FROM ".TABLE_PREFIX."users WHERE usermap_lat!='0' AND usermap_lon!='0'");
		while($users = $db->fetch_array($query))
		{
			// Create a formatted link to the profile of the user
			$username = build_profile_link(format_name($users['username'], $users['usergroup'], $users['displaygroup']), $users['uid']);
			
			// Load the avatar of the user, if applicable
			if(!empty($users['avatar']))
			{
				$avatar = "<br /><img src=\"".$users['avatar']."\" alt=\"\" />";
			}
			else
			{
				$avatar = "";
			}
			
			// Plugin hook
			$plugins->run_hooks("username_default_while");
			
			eval("\$usermap_pins_bit_user = \"".$templates->get("usermap_pins_bit_user", 1, 0)."\";");
			// TODO: Wouldn't it be possible to replace " by \" instead of '
			$usermap_pins_bit_user = str_replace("\"", "'", $usermap_pins_bit_user);
			$usermap_pins_bit_user = str_replace("\n", "", $usermap_pins_bit_user);
			
			// Add user to pin on the specified location
			$userpins[$users['usermap_lat'].", ".$users['usermap_lon']][] = $usermap_pins_bit_user;
		}
		
		// If there are pins added, show them on the map
		if(is_array($userpins))
		{
			$count = 0;
			foreach($userpins as $coordinates => $userpin)
			{
				$count++;
				$window = implode($userpin, "<br /><br />");
				eval("\$usermap_pins_bit .= \"".$templates->get("usermap_pins_bit")."\";");
			}
		}
		
		eval("\$usermap_pins = \"".$templates->get("usermap_pins")."\";");
		
		
		// If the user is logged in, and he has the needed permissions,
		// show him the form to add a pin.
		if($mybb->user['uid'] != 0 && $mybb->usergroup['canaddusermappin'] == 1)
		{
			eval("\$usermap_form = \"".$templates->get("usermap_form")."\";");
		}
		
		eval("\$usermap = \"".$templates->get("usermap")."\";");
		output_page($usermap);
	break;
	case 'lookup':
		require_once MYBB_ROOT."inc/class_xml.php";
		
		// Does the user has the required permissions to continue?
		if($mybb->user['uid'] == 0 || $mybb->usergroup['canaddusermappin'] == 0)
		{
			error_no_permission();
		}
		
		if(!isset($mybb->input['address']))
		{
			error($lang->noinput, $lang->error);
		}
		else
		{
			//Load the xml-file of Google for the given place
			$lookup_file = file_get_contents("https://maps.googleapis.com/maps/api/geocode/xml?address=".urlencode($mybb->input['address'])."&sensor=false");
			
			//Parse the xml-file
			$parser = new XMLParser($lookup_file);
			$lookup = $parser->get_tree();
			
			// TODO: https://developers.google.com/maps/documentation/geocoding/?hl=nl#StatusCodes
			// So, some other errors might be usefull
			if($lookup['GeocodeResponse']['status']['value'] != "OK")
			{
				error($lang->coordinatesnotfound, $lang->error);
			}
			else
			{
				//Load response
				if(!isset($lookup['GeocodeResponse']['result']['geometry']['location']))
				{
					$response = $lookup['GeocodeResponse']['result'][0]['geometry']['location'];
				}
				else
				{
					$response = $lookup['GeocodeResponse']['result']['geometry']['location'];
				}
				
				redirect("usermap.php?action=pin&lat=".$response['lat']['value']."&lon=".$response['lng']['value']."&address=".$mybb->input['address'], $lang->coordinatesfound);
			}
		}
	break;
	case 'pin':
		//Guests
		if($mybb->user['uid'] == 0 || $mybb->usergroup['canaddusermappin'] == 0)
		{
			error_no_permission();
		}
		
		if(!floatval($mybb->input['lat']) || !floatval($mybb->input['lon']) || !isset($mybb->input['address']))
		{
			error($lang->noinput, $lang->error);
		}
		else
		{
			/***********************************
			 *   Defaults
			 ***********************************/
			$defaults = $cache->read("usermap");
			
			/***********************************
			 *   Pin
			 ***********************************/
			//Username
			$username = build_profile_link(format_name($mybb->user['username'], $mybb->user['usergroup'], $mybb->user['displaygroup']), $mybb->user['uid']);
			
			//Avatar
			if(!empty($mybb->user['avatar']))
			{
				$avatar = "<br /><img src=\"".$mybb->user['avatar']."\" alt=\"\" />";
			}
			
			//Vars
			$users['usermap_lat'] = floatval($mybb->input['lat']);
			$users['usermap_lon'] = floatval($mybb->input['lon']);
			$coordinates = $users['usermap_lat'].", ".$users['usermap_lon'];
			
			//Templates
			eval("\$userpin['window'] = \"".$templates->get("usermap_pins_bit_user", 1, 0)."\";");
			// TODO: Equivalent to the note on the main page
			$userpin['window'] = str_replace("\"", "'", $userpin['window']);
			$userpin['window'] = str_replace("\n", "", $userpin['window']);
			eval("\$usermap_pins_bit .= \"".$templates->get("usermap_pins_bit")."\";");
			eval("\$usermap_pins = \"".$templates->get("usermap_pins")."\";");
			
			eval("\$usermap = \"".$templates->get("usermap_pin")."\";");
			output_page($usermap);
		}
	break;
	case 'do_pin':
		//Guests
		if($mybb->user['uid'] == 0 || $mybb->usergroup['canaddusermappin'] == 0)
		{
			error_no_permission();
		}
		
		if(!floatval($mybb->input['lat']) || !floatval($mybb->input['lon']))
		{
			error($lang->noinput, $lang->error);
		}
		else
		{
			$pin = array(
				'usermap_lat'		=> floatval($mybb->input['lat']),
				'usermap_lon'		=> floatval($mybb->input['lon'])
			);
			
			$plugins->run_hooks("usermap_do_pin");
			
			$db->update_query("users", $pin, "uid='".$mybb->user['uid']."'");
			
			redirect("usermap.php", $lang->pinsaved);
		}
	break;
}
?>
