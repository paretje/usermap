<?php
/***************************************************************************
 *
 *   Usermap-system for MyBB
 *   Copyright: © 2008-2014 Online - Urbanus
 *   
 *   Website: http://www.Online-Urbanus.be
 *   
 *   Last modified: 03/01/2014 by Paretje
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

// TODO: Remove duppliation of the definition of the navigation tabs
// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

//Require
require_once MYBB_ROOT."inc/functions_upload.php";

//Plugin
$plugins->run_hooks("admin_usermap_start");

//Navigation
$page->add_breadcrumb_item($lang->usermap, "index.php?module=config/usermap");

if($mybb->input['action'] == "add_place")
{
	//Handle the place
	if($mybb->request_method == "post")
	{
		//Check values
		if(empty($mybb->input['name']))
		{
			$errors[] = $lang->error_missing_name;
		}
		if(empty($mybb->input['lat']))
		{
			$errors[] = $lang->error_missing_lat;
		}
		if(empty($mybb->input['lon']))
		{
			$errors[] = $lang->error_missing_lon;
		}
		if(!intval($mybb->input['zoom']))
		{
			$errors[] = $lang->error_missing_zoom;
		}
		if(!intval($mybb->input['displayorder']))
		{
			$errors[] = $lang->error_missing_displayorder;
		}
		if(!isset($mybb->input['default']) || ($mybb->input['default'] != "yes" && $mybb->input['default'] != "no"))
		{
			$errors[] = $lang->error_missing_default;
		}
		
		//Check if there were errors, if no, continue
		if(!$errors)
		{
			//Insert place
			$insert_place = array(
				"name"			=> addslashes($mybb->input['name']),
				"lat"			=> floatval($mybb->input['lat']),
				"lon"			=> floatval($mybb->input['lon']),
				"zoom"			=> intval($mybb->input['zoom']),
				"displayorder"		=> intval($mybb->input['displayorder'])
			);
			
			$pid = $db->insert_query("usermap_places", $insert_place);
			
			//Update default cache
			if($mybb->input['default'] == "yes")
			{
				$defaults = $cache->read("usermap");
				$defaults['place'] = $pid;
				$cache->update("usermap", $defaults);
			}
			
			//Log
			log_admin_action($pid, $mybb->input['name']);
			
			flash_message($lang->added_place, 'success');
			admin_redirect("index.php?module=config/usermap");
		}
	}
	
	//Navigation and header
	$page->add_breadcrumb_item($lang->nav_add_place);
	$page->output_header($lang->nav_add_place);
	
	//Show the sub-tabs
	$sub_tabs = array();
	$sub_tabs['manage_places'] = array(
		'title' => $lang->nav_manage_places,
		'link' => "index.php?module=config/usermap",
		'description' => $lang->nav_manage_places_desc
	);
	$sub_tabs['add_place'] = array(
		'title' => $lang->nav_add_place,
		'link' => "index.php?module=config/usermap&amp;action=add_place",
		'description' => $lang->nav_add_place_desc
	);
	
	$page->output_nav_tabs($sub_tabs, 'add_place');
	
	//Show the errors
	if($errors)
	{
		$page->output_inline_error($errors);
	}
	
	//Selected items
	if(!isset($mybb->input['zoom']))
	{
		$mybb->input['zoom'] = 4;
	}
	if(!isset($mybb->input['displayorder']))
	{
		$mybb->input['displayorder'] = 1;
	}
	if(!isset($mybb->input['default']))
	{
		$mybb->input['default'] = "no";
	}
	
	//Starts the table and the form
	$form = new Form("index.php?module=config/usermap&amp;action=add_place", "post");
	$form_container = new FormContainer($lang->nav_add_place);
	
	//Input for place
	$form_container->output_row($lang->place_name." <em>*</em>", false, $form->generate_text_box('name', $mybb->input['name'], array('id' => 'name')), 'name');
	$form_container->output_row($lang->place_lat." <em>*</em>", false, $form->generate_text_box('lat', $mybb->input['lat'], array('id' => 'lat')), 'lat');
	$form_container->output_row($lang->place_lon." <em>*</em>", false, $form->generate_text_box('lon', $mybb->input['lon'], array('id' => 'lon')), 'lon');
	$form_container->output_row($lang->place_zoom." <em>*</em>", false, $form->generate_text_box('zoom', $mybb->input['zoom'], array('id' => 'zoom')), 'zoom');
	$form_container->output_row($lang->place_displayorder." <em>*</em>", false, $form->generate_text_box('displayorder', $mybb->input['displayorder'], array('id' => 'displayorder')), 'displayorder');
	$form_container->output_row($lang->place_default." <em>*</em>", false, $form->generate_yes_no_radio('default', $mybb->input['default'], false), 'default');
	
	//End of table and form
	$form_container->end();
	$buttons[] = $form->generate_submit_button($lang->save);
	$buttons[] = $form->generate_reset_button($lang->reset);
	$form->output_submit_wrapper($buttons);
	$form->end();
	$page->output_footer();
}
elseif($mybb->input['action'] == "edit_place")
{
	//Test place
	$query = $db->query("SELECT * FROM ".TABLE_PREFIX."usermap_places WHERE pid='".intval($mybb->input['pid'])."'");
	$place = $db->fetch_array($query);
	$place_test = $db->num_rows($query);
	
	if($place_test == 0)
	{
		flash_message($lang->placedoesntexist, 'error');
		admin_redirect("index.php?module=config/usermap");
	}
	
	//Handle the place
	if($mybb->request_method == "post")
	{
		//Check values
		if(empty($mybb->input['name']))
		{
			$errors[] = $lang->error_missing_name;
		}
		if(empty($mybb->input['lat']))
		{
			$errors[] = $lang->error_missing_lat;
		}
		if(empty($mybb->input['lon']))
		{
			$errors[] = $lang->error_missing_lon;
		}
		if(!intval($mybb->input['zoom']))
		{
			$errors[] = $lang->error_missing_zoom;
		}
		if(!intval($mybb->input['displayorder']))
		{
			$errors[] = $lang->error_missing_displayorder;
		}
		if(!isset($mybb->input['default']) || ($mybb->input['default'] != "yes" && $mybb->input['default'] != "no"))
		{
			$errors[] = $lang->error_missing_default;
		}
		
		//Check if there were errors, if no, continue
		if(!$errors)
		{
			//Update place
			$update_place = array(
				"name"			=> addslashes($mybb->input['name']),
				"lat"			=> floatval($mybb->input['lat']),
				"lon"			=> floatval($mybb->input['lon']),
				"zoom"			=> intval($mybb->input['zoom']),
				"displayorder"		=> intval($mybb->input['displayorder'])
			);
			
			$db->update_query("usermap_places", $update_place, "pid='".intval($mybb->input['pid'])."'");
			
			//Update default cache
			if($mybb->input['default'] == "yes")
			{
				$defaults = $cache->read("usermap");
				$defaults['place'] = intval($mybb->input['pid']);
				$cache->update("usermap", $defaults);
			}
			
			//Log
			log_admin_action($mybb->input['pid'], $mybb->input['name']);
			
			flash_message($lang->edited_place, 'success');
			admin_redirect("index.php?module=config/usermap");
		}
	}
	
	//Navigation and header
	$page->add_breadcrumb_item($lang->nav_edit_place);
	$page->output_header($lang->nav_edit_place);
	
	//Show the sub-tabs
	$sub_tabs = array();
	$sub_tabs['manage_places'] = array(
		'title' => $lang->nav_manage_places,
		'link' => "index.php?module=config/usermap",
		'description' => $lang->nav_manage_places_desc
	);
	$sub_tabs['edit_place'] = array(
		'title' => $lang->nav_edit_place,
		'link' => "index.php?module=config/usermap&amp;action=edit_place&amp;cid=".$mybb->input['cid'],
		'description' => $lang->nav_edit_place_desc
	);
	
	$page->output_nav_tabs($sub_tabs, 'edit_place');
	
	//Show the errors
	if($errors)
	{
		$page->output_inline_error($errors);
	}
	
	//Default
	$defaults = $cache->read("usermap");
	if($defaults['place'] == $place['pid'])
	{
		$place['default'] = "yes";
	}
	else
	{
		$place['default'] = "no";
	}
	
	//Starts the table and the form
	$form = new Form("index.php?module=config/usermap&amp;action=edit_place", "post");
	echo $form->generate_hidden_field("pid", $mybb->input['pid']);
	$form_container = new FormContainer($lang->nav_edit_place);
	
	//Input for place
	$form_container->output_row($lang->place_name." <em>*</em>", false, $form->generate_text_box('name', $place['name'], array('id' => 'name')), 'name');
	$form_container->output_row($lang->place_lat." <em>*</em>", false, $form->generate_text_box('lat', $place['lat'], array('id' => 'lat')), 'lat');
	$form_container->output_row($lang->place_lon." <em>*</em>", false, $form->generate_text_box('lon', $place['lon'], array('id' => 'lon')), 'lon');
	$form_container->output_row($lang->place_zoom." <em>*</em>", false, $form->generate_text_box('zoom', $place['zoom'], array('id' => 'zoom')), 'zoom');
	$form_container->output_row($lang->place_displayorder." <em>*</em>", false, $form->generate_text_box('displayorder', $place['displayorder'], array('id' => 'displayorder')), 'displayorder');
	$form_container->output_row($lang->place_default." <em>*</em>", false, $form->generate_yes_no_radio('default', $place['default'], false), 'default');
	
	//End of table and form
	$form_container->end();
	$buttons[] = $form->generate_submit_button($lang->save);
	$buttons[] = $form->generate_reset_button($lang->reset);
	$form->output_submit_wrapper($buttons);
	$form->end();
	$page->output_footer();
}
elseif($mybb->input['action'] == "delete_place")
{
	//Test place
	$query = $db->query("SELECT * FROM ".TABLE_PREFIX."usermap_places WHERE pid='".intval($mybb->input['pid'])."'");
	$place = $db->fetch_array($query);
	$place_test = $db->num_rows($query);
	
	if($place_test == 0)
	{
		flash_message($lang->placedoesntexist, 'error');
		admin_redirect("index.php?module=config/usermap");
	}
	
	// User clicked no
	if($mybb->input['no'])
	{
		admin_redirect("index.php?module=config/usermap");
	}
	
	//Handle the category
	if($mybb->request_method == "post")
	{
		//Delete place
		$db->delete_query("usermap_places", "pid='".intval($mybb->input['pid'])."'");
		
		//Update default cache
		$defaults = $cache->read("usermap");
		if($defaults['place'] == intval($mybb->input['pid']))
		{
			//Load new place
			$query = $db->query("SELECT * FROM ".TABLE_PREFIX."usermap_places ORDER BY displayorder ASC LIMIT 0,1");
			$place = $db->fetch_array($query);
			
			$defaults['place'] = $place['pid'];
			$cache->update("usermap", $defaults);
		}
		
		//Log
		log_admin_action($mybb->input['pid'], $place['name']);
		
		flash_message($lang->deleted_place, 'success');
		admin_redirect("index.php?module=config/usermap");
	}
	else
	{
		$page->output_confirm_action("index.php?module=config/usermap&action=delete_place&pid=".$mybb->input['pid'], $lang->delete_place_confirmation);
	}
}
elseif($mybb->input['action'] == "order_places")
{
	if(!is_array($mybb->input['displayorder']))
	{
		flash_message($lang->error_missing_order, 'error');
		admin_redirect("index.php?module=config/usermap");
	}
	else
	{
		foreach($mybb->input['displayorder'] as $pid => $number)
		{
			$place = array(
				"displayorder"		=> intval($number)
			);
			
			$db->update_query("usermap_places", $place, "pid='".intval($pid)."'");
		}
		
		//Log
		log_admin_action();
		
		flash_message($lang->ordered_places, 'success');
		admin_redirect("index.php?module=config/usermap");
	}
}
else
{
	//Navigation and header
	$page->output_header($lang->usermap);
	
	//Show the sub-tabs
	$sub_tabs = array();
	$sub_tabs['manage_places'] = array(
		'title' => $lang->nav_manage_places,
		'link' => "index.php?module=config/usermap",
		'description' => $lang->nav_manage_places_desc
	);
	$sub_tabs['add_place'] = array(
		'title' => $lang->nav_add_place,
		'link' => "index.php?module=config/usermap&amp;action=add_place",
		'description' => $lang->nav_add_place_desc
	);
	
	$page->output_nav_tabs($sub_tabs, 'manage_places');
	
	//Start table and form
	$form = new Form("index.php?module=config/usermap&amp;action=order_places", "post");
	
	$table = new Table;
	$table->construct_header($lang->places);
	$table->construct_header($lang->order, array("class" => "align_center", "width" => "5%"));
	$table->construct_header($lang->controls, array("class" => "align_center", "width" => 150));
	
	//Load the places
	$query = $db->query("SELECT * FROM ".TABLE_PREFIX."usermap_places ORDER BY displayorder ASC");
	$places_test = $db->num_rows($query);
	
	//Test places
	if($places_test == 0)
	{
		$page->output_error("<p><em>".$lang->no_places."</em></p>");
		$page->output_footer();
	}
	
	while($places = $db->fetch_array($query))
	{
		//Controls
		$popup = new PopupMenu("pid".$places['pid'], $lang->options);
		$popup->add_item($lang->edit, "index.php?module=config/usermap&amp;action=edit_place&amp;pid=".$places['pid']);
		$popup->add_item($lang->delete, "index.php?module=config/usermap&amp;action=delete_place&amp;pid=".$places['pid']."&amp;my_post_key=".$mybb->post_code, "return AdminCP.deleteConfirmation(this, '".$lang->delete_place_confirmation."')");
		
		//Output row
		$table->construct_cell("<strong>".$places['name']."</strong>");
		$table->construct_cell($form->generate_text_box("displayorder[".$places['pid']."]", $places['displayorder'], array('style' => 'width: 80%;')), array('class' => 'align_center'));
		$table->construct_cell($popup->fetch(), array("class" => "align_center"));
		
		$table->construct_row();
	}
	
	//End of table, form and AdminCP footer
	$table->output($lang->places);
	$buttons[] = $form->generate_submit_button($lang->save_order);
	$buttons[] = $form->generate_reset_button($lang->reset);
	$form->output_submit_wrapper($buttons);
	$form->end();
	$page->output_footer();
}
?>
