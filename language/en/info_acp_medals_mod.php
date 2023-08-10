<?php
/***************************************************************************
*
* @package Medals Mod for phpBB3
* @version $Id: medals.php,v 0.7.0 2008/01/14 Gremlinn$
* @copyright (c) 2008 Nathan DuPra (mods@dupra.net)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
***************************************************************************/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'IMG_ICON_POST_APPROVE'			=> 'Approve',
	'ACP_MEDALS_INDEX'				=> 'Badges ACP',
	'ACP_MEDALS_INDEX_EXPLAIN'		=> 'Badges Index Explain',
	'ACP_MEDALS_TITLE'				=> 'Badges Management',
	'ACP_MEDALS_SETTINGS'			=> 'Configuration',

	'MEDALS_MOD_INSTALLED'			=> 'Badges Extension version %s installed',
	'MEDALS_MOD_UPDATED'			=> 'Badges Extension updated to version %s',
	'MEDALS_MOD_MANUAL'				=> 'You have an older version of Badges Extension installed.<br />You will need to uninstall that version first<br />Be sure to make backups first.',

	'ACL_U_AWARD_MEDALS'			=> 'Can award badges to users',
	'ACL_U_NOMINATE_MEDALS'			=> 'Can nominate badges to other users',
	'ACL_A_MANAGE_MEDALS'			=> 'Can use the badges management module',

// Badges Management
	'ACP_MEDAL_MGT_TITLE'				=> 'Badge Management',
	'ACP_MEDAL_MGT_DESC'				=> 'Here you can view, create, modify, and delete badge categories',

	'ACP_MEDALS'						=> 'Badges',
	'ACP_MEDALS_DESC'					=> 'Here you can view, create, modify, and delete badges for this category.',
	'ACP_MULT_TO_USER'					=> 'Number of Awards per user',
	'ACP_USER_NOMINATED'				=> 'User Nominated',
	'ACP_MEDAL_LEGEND'					=> 'Badge',
	'ACP_MEDAL_TITLE_EDIT'				=> 'Edit Badge',
	'ACP_MEDAL_TEXT_EDIT'				=> 'Modify an existing badge',
	'ACP_MEDAL_TITLE_ADD'				=> 'Create Badge',
	'ACP_MEDAL_TEXT_ADD'				=> 'Create a new badge from scratch',
	'ACP_MEDAL_DELETE_GOOD'				=> 'The badge was removed successfully.<br /><br /> Click <a href="%s">here</a> to return to the previous category',
	'ACP_MEDAL_EDIT_GOOD'				=> 'The badge was updated successfully.<br /><br /> Click <a href="%s">here</a> to go the badge\'s category',
	'ACP_MEDAL_ADD_GOOD'				=> 'The badge was added successfully.<br /><br /> Click <a href="%s">here</a> to go the badge\'s category',
	'ACP_CONFIRM_MSG_1'					=> 'Are you sure you wish to delete this badge? This will also delete this badge from any users that have it. <br /><br /><form method="post"><fieldset class="submit-buttons"><input class="button1" type="submit" name="confirm" value="Yes" />&nbsp;<input type="submit" class="button2" name="cancelbadge" value="No" /></fieldset></form>',
	'ACP_NAME_TITLE'					=> 'Badge Name',
	'ACP_NAME_DESC'						=> 'Badge Description',
	'ACP_IMAGE_TITLE'					=> 'Badge Image',
	'ACP_IMAGE_EXPLAIN'					=> 'The gif image for the badge inside the images/badges/ directory',
	'ACP_DEVICE_TITLE'					=> 'Device Image',
	'ACP_DEVICE_EXPLAIN'				=> 'The base name of the gif image inside the images/badges/devices directory, to be applied to dynamically create badges.<br /> Ex. device-2.gif = device',
	'ACP_PARENT_TITLE'					=> 'Badge Category',
	'ACP_PARENT_EXPLAIN'				=> 'The category that the badge is to be put in',
	'ACP_DYNAMIC_TITLE'					=> 'Dynamic Badge Image',
	'ACP_DYNAMIC_EXPLAIN'				=> 'Dynamically create the image for multiple awardings.',
	'ACP_NOMINATED_TITLE'				=> 'Badge Nominations',
	'ACP_NOMINATED_EXPLAIN'				=> 'Can users nominate other users for this badge?',
	'ACP_CREATE_MEDAL'					=> 'Create Badge',
	'ACP_NO_MEDALS'						=> 'No Badges',
	'ACP_NUMBER'						=> 'Number of Awards',
	'ACP_NUMBER_EXPLAIN'				=> 'Defines how many times this badge can be awarded to a user.',
	'ACP_POINTS'						=> 'Points',
	'ACP_POINTS_EXPLAIN'				=> 'Defines how points are awarded (or subtracted) for receiving this badge.<br />Works with Ultimate Points Mod.',

	'ACP_MEDALS_MGT_INDEX'				=> 'Badge Categories',
	'ACP_MEDAL_TITLE_CAT'				=> 'Edit Category',
	'ACP_MEDAL_TEXT_CAT'				=> 'Modify an existing category',
	'ACP_MEDAL_LEGEND_CAT'				=> 'Category',
	'ACP_NAME_TITLE_CAT'				=> 'Category Name',
	'ACP_CREATE_CAT'					=> 'Create Category',
	'ACP_CAT_ADD_FAIL'					=> 'No category name was listed for addition.<br /><br /> Click <a href="%s">here</a> to return the categories list page',
	'ACP_CAT_ADD_GOOD'					=> 'The category was added successfully.<br /><br /> Click <a href="%s">here</a> to return the categories list page',
	'ACP_CAT_EDIT_GOOD'					=> 'The category was edited successfully.<br /><br /> Click <a href="%s">here</a> to return the categories list page',
	'ACP_CAT_DELETE_CONFIRM'			=> 'Which category would you like to move all this category\'s badges to upon deletion? <br /><form method="post"><fieldset class="submit-buttons"><select name="newcat">%s</select><br /><br /><input class="button1" type="submit" name="moveall" value="Move All Badges" />&nbsp;<input class="button2" type="submit" name="deleteall" value="Delete All Badges" />&nbsp;<input type="submit" class="button2" name="cancelcat" value="Cancel Deletion" /></fieldset></form>',
	'ACP_CAT_DELETE_CONFIRM_ELSE'		=> 'There are no other categories to move these badges to.<br />Are you sure you wish to remove this category and all of its badges?<br /><form method="post"><fieldset class="submit-buttons"><br /><input class="button2" type="submit" name="deleteall" value="Yes" />&nbsp;<input type="submit" class="button2" name="cancelcat" value="No" /></fieldset></form>',
	'ACP_CAT_DELETE_GOOD'				=> 'This category, all of its contents, and all of its contents that were awarded were deleted successfully<br /><br /> Click <a href="%s">here</a> to return the categories list page',
	'ACP_CAT_DELETE_MOVE_GOOD'			=> 'All badges from "%1$s" have been moved to "%2$s" and the category has been deleted successfully.<br /><br /> Click <a href="%3$s">here</a> to return the categories list page',
	'ACP_NO_CAT_ID'						=> 'No Categories',

// Badges Configuration
	'ACP_CONFIG_TITLE'					=> 'Badges Configuration',
	'ACP_CONFIG_DESC'					=> 'Here you can set options for the Badge System 0.21.0',
	'ACP_MEDALS_CONF_SETTINGS'			=> 'Badges Configuration Settings',
	'ACP_MEDALS_CONF_SAVED'				=> 'Badges configuration saved<br /><br /> Click <a href="%s">here</a> to go the Badge\'s ACP Configuration',
	'ACP_MEDALS_IMG_PATH'				=> 'Image path',
	'ACP_MEDALS_IMG_PATH_EXPLAIN'		=> 'Enter the path relative to the forum root to where the images will be stored at starting and ending with a slash (/).',
	'ACP_MEDALS_SM_IMG_WIDTH'			=> 'Small badge image width',
	'ACP_MEDALS_SM_IMG_WIDTH_EXPLAIN'	=> 'The width (in pixels) to for badges displayed in the viewtopic and profile badge information section.<br />Set to 0 to not define a width.',
	'ACP_MEDALS_SM_IMG_HT'				=> 'Small badge image height',
	'ACP_MEDALS_SM_IMG_HT_EXPLAIN'		=> 'The height (in pixels) to for badges displayed in the viewtopic and profile badge information section.<br />Set to 0 to not define a height.',
	'ACP_MEDALS_VT_SETTINGS'			=> 'Viewtopic Display Settings',
	'ACP_MEDALS_TOPIC_DISPLAY'			=> 'Allow Badge Display in Viewtopic',
	'ACP_MEDALS_TOPIC_ROW'				=> 'Number of badges across',
	'ACP_MEDALS_TOPIC_ROW_EXPLAIN'		=> 'Number of badges to display in the Viewtopic across.',
	'ACP_MEDALS_TOPIC_COL'				=> 'Number of badges down',
	'ACP_MEDALS_TOPIC_COL_EXPLAIN'		=> 'Number of badges to display in the Viewtopic down.',
	'ACP_MEDALS_PROFILE_ACROSS'			=> 'Badges to display in profile',
	'ACP_MEDALS_PROFILE_ACROSS_EXPLAIN'	=> 'Number of badges to display in the profile badge information section.',
	'ACP_MEDALS_ACTIVATE' 				=> 'Badges Extension Activated',
));
