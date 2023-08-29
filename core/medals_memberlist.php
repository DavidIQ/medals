<?php
/**
*
* @author Gremlinn (Nathan DuPra) mods@dupra.net | Anvar Stybaev (DEV Extension phpBB3.1.x)
* @package Medals System Extension
* @copyright Anvar 2015 (c) Extensions bb3.mobi
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace bb3mobi\medals\core;

class medals_memberlist
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var string */
	protected $tb_medals;

	/** @var string */
	protected $tb_medals_awarded;

	/** @var string */
	protected $tb_medals_cats;

	const HOUR_TTL = 3600;

	public function __construct(\phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\template\template $template, \phpbb\db\driver\driver_interface $db, $tb_medals, $tb_medals_awarded, $tb_medals_cats, $helper)
	{
		$this->user = $user;
		$this->auth = $auth;
		$this->config = $config;
		$this->template = $template;
		$this->db = $db;
		$this->tb_medals = $tb_medals;
		$this->tb_medals_awarded = $tb_medals_awarded;
		$this->tb_medals_cats = $tb_medals_cats;

		$this->helper = $helper;

		$this->user->add_lang_ext('bb3mobi/medals', 'info_medals_mod');
	}

	public function medal_row($user_id)
	{
		//
		// Category
		//
		$sql = "SELECT id, name
			FROM " . $this->tb_medals_cats . "
			ORDER BY order_id";
		$result = $this->db->sql_query($sql, self::HOUR_TTL);
		$category_rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		if (!count($category_rows))
		{
			return;
		}
		
		$sql = "SELECT m.id, m.name, m.description, m.image, m.device, m.dynamic, m.parent,
					ma.nominated_reason, ma.time, ma.awarder_id, ma.awarder_un, ma.awarder_color, ma.bbuid, ma.bitfield,
					c.id as cat_id, c.name as cat_name
				FROM " . $this->tb_medals . " m
				JOIN " . $this->tb_medals_awarded . " ma ON m.id = ma.medal_id
				JOIN " . $this->tb_medals_cats . " c ON m.parent = c.id
				WHERE ma.user_id = {$user_id}
				AND ma.nominated = 0
				ORDER BY c.order_id, m.order_id, ma.time";
		$result = $this->db->sql_query($sql);
		$user_awards_row = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);
				
		if (!count($user_awards_row))
		{
			return;
		}
		
		$medal_count = count($user_awards_row);
		if ($medal_count)
		{
			$this->template->assign_var('U_MEDALS_VIEW', $this->helper->route('bb3mobi_medals_awardsage', ['user_id' => $user_id]));
		}

		for ($i = 0; $i < count($category_rows); $i++)
		{
			$cat_id = $category_rows[$i]['id'];
			$rowset = [];
			foreach ($user_awards_row as $row)
			{
				$medal_id = $row['id'];
				if (!isset($rowset[$medal_id]))
				{
					$rowset[$medal_id]['name'] = $row['name'];
					$rowset[$medal_id]['cat_id'] = $row['cat_id'];
					$rowset[$medal_id]['cat_name'] = $row['cat_name'];
					if (isset($rowset[$medal_id]['description']))
					{
						$rowset[$medal_id]['description'] .= $row['description'];
					}
					else
					{
						$rowset[$medal_id]['description'] = $row['description'];
					}
					$rowset[$medal_id]['image'] = generate_board_url() . $this->config['medals_images_path'] . $row['image'];
					$rowset[$medal_id]['device'] = generate_board_url() . $this->config['medals_images_path'] . 'devices/' . $row['device'];
					$rowset[$medal_id]['dynamic'] = $row['dynamic'];
				}
				if (isset($rowset[$medal_id]['medal_count']))
				{
					$rowset[$medal_id]['medal_count'] += '1';
				}
				else
				{
					$rowset[$medal_id]['medal_count'] = '1';
				}
			}

			$medal_width = ($this->config['medal_small_img_width']) ? ' width="'.$this->config['medal_small_img_width'].'"' : '';
			$medal_height = ($this->config['medal_small_img_ht']) ? ' height="'.$this->config['medal_small_img_ht'].'"' : '';

			//
			// Should we display this category/medal set?
			//
			$display_medal = 0;
			$numberofmedals = 0;
			$after_first_cat = 0;
			$newcat = 1;

			foreach ($rowset as $medal_id => $data)
			{
				$medal_name = $data['name'];
				if ($cat_id == $data['cat_id'])
				{
					$display_medal = 1;
				}

				$display_across = $this->config['medal_profile_across'] ? $this->config['medal_profile_across'] : 5 ;
				if ($numberofmedals == $display_across)
				{
					$break = '<br />' ;
					$numberofmedals = 0 ;
				}
				else
				{
					$break = '' ;
				}

				if (!empty($newcat) && !empty($after_first_cat))
				{
					$break = '<hr />&nbsp;' ;
					$numberofmedals = 0 ;
				}

				$numberofmedals++ ;
				if (!empty($display_medal))
				{
					if ($data['medal_count'] > 1)
					{
						if ($data['dynamic'])
						{
							$img_medals = $this->helper->route('bb3mobi_medals_controller', [
									'm' => 'mi',
									'med' => $data['image'],
									'd' => $data['device'] . '-' . ($data['medal_count'] - 1) . '.gif'
								]
							);

							$small_image = $break . '<img src="' . $img_medals . '" alt="' . $medal_name . '" title="' . $medal_name . '"' . $medal_width . $medal_height . ' />' ;
						}
						else
						{
							$cluster = '-' . $data['medal_count'] ;
							$device_image = substr_replace($data['image'],$cluster, -4) . substr($data['image'], -4);
							if (file_exists($device_image))
							{
								$data['image'] = $device_image;
							}
							$small_image = $break . '<img src="' . $data['image'] . '" alt="' . $medal_name . '" title="' . $medal_name . '"' . $medal_width . $medal_height . ' />';
						}
					}
					else
					{
						$small_image = $break . '<img src="' . $data['image'] . '" alt="' . $medal_name . '" title="' . $medal_name . '"' . $medal_width . $medal_height . ' />';
					}

					$this->template->assign_block_vars('switch_display_medal.details', [
						'MEDAL_IMAGE_SMALL' => $small_image,
					]);
					$display_medal = 0;
					$newcat = 0 ;
				}
				else
				{
					// New category lets put an hr between
					$newcat = 1 ;
					$after_first_cat = 1;
				}
			}
		}
	}
}
