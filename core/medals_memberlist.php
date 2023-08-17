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
		$s_nominate = false;

		if ($this->auth->acl_get('u_nominate_medals') && $user_id != $this->user->data['user_id'])
		{
			$s_nominate = true;
		}

		$is_mod = ($this->user->data['user_type'] == USER_FOUNDER || $this->auth->acl_get('u_award_medals')) ? true : false;

		$uid			= $bitfield			= '';	// will be modified by generate_text_for_storage
		$allow_bbcode	= $allow_smilies	= true;
		$allow_urls		= false;
		$m_flags = '3';  // 1 is bbcode, 2 is smiles, 4 is urls (add together to turn on more than one)
		//
		// Category
		//

		$sql = "SELECT id, name
			FROM " . $this->tb_medals_cats . "
			ORDER BY order_id";

		if (!($result = $this->db->sql_query($sql, self::HOUR_TTL)))
		{
			die('Could not query medal categories list');
		}

		$category_rows = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$category_rows[] = $row;
		}
		$this->db->sql_freeresult($result);

		$sql = "SELECT COUNT(m.medal_id) AS medal_count
			FROM " . $this->tb_medals_awarded . " m
			WHERE m.user_id = {$user_id}
				AND m.nominated = 0";
		$result = $this->db->sql_query($sql);
		$medal_count = $this->db->sql_fetchfield('medal_count');
		$this->db->sql_freeresult($result);

		if ($medal_count)
		{
			$this->template->assign_block_vars('switch_display_medal', []);

			$this->template->assign_block_vars('switch_display_medal.medal', [
				'MEDAL_BUTTON' => '<input type="button" class="button2" onclick="hdr_toggle(\'toggle_medal\',\'medal_open_close\')" value="' . $this->user->lang['MEDALS_VIEW_BUTTON'] . '"/>'
			]);
		}

		$u_nominate = '';
		if ($s_nominate)
		{
			$u_nominate = $this->helper->route('bb3mobi_medals_controller', ['m' => 'nominate', 'u' => $user_id]);
		}

		$u_can_award = '';
		if ($this->auth->acl_get('a_user') || $is_mod)
		{
			$u_can_award = $this->helper->route('bb3mobi_medals_controller', ['m' => 'award', 'u' => $user_id]);
		}

		$this->template->assign_vars([
			'USER_ID'				=> $user_id,
			'U_NOMINATE'			=> $u_nominate,
			'U_CAN_AWARD_MEDALS'	=> $u_can_award,
			'L_USER_MEDAL'			=> $this->user->lang['MEDALS'],
			'USER_MEDAL_COUNT'		=> $medal_count,
			'L_MEDAL_INFORMATION'	=> $this->user->lang['MEDAL_INFORMATION'],
			'L_MEDAL_NAME'			=> $this->user->lang['MEDAL'],
			'L_MEDAL_DETAIL'		=> $this->user->lang['MEDAL_DETAIL'],
		]);

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
		$user_awards_row = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_awards_row[] = $row;
		}
		$this->db->sql_freeresult($result);

		if (!count($user_awards_row))
		{
			return;
		}

		for ($i = 0; $i < count($category_rows); $i++)
		{
			$cat_id = $category_rows[$i]['id'];
			$rowset = [];
			$medal_time = $this->user->lang['AWARD_TIME'] . ':&nbsp;';
			$medal_reason = $this->user->lang['MEDAL_AWARD_REASON'] . ':&nbsp;';
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
				$row['nominated_reason'] = ($row['nominated_reason']) ? $row['nominated_reason'] : 'Medal_no_reason';
				$awarder_name = "";
				if ($row['awarder_id'])
				{
					$awarder_name = "<br />" . $this->user->lang['AWARDED_BY'] . ": " . get_username_string('full', $row['awarder_id'], $row['awarder_un'], $row['awarder_color'], $row['awarder_un']) ;
				}
				//generate_text_for_storage($row['nominated_reason'], $uid, $bitfield, $m_flags, $allow_bbcode, $allow_urls, $allow_smilies);
				$reason = generate_text_for_display($row['nominated_reason'], $row['bbuid'], $row['bitfield'], $m_flags);
				if (isset($rowset[$medal_id]['medal_issue']))
				{
					$rowset[$medal_id]['medal_issue'] .= $medal_time . $this->user->format_date($row['time']) . $awarder_name . '</td></tr><tr><td>' . $medal_reason . '<div class="content">' . $reason . '</div><hr />';
				}
				else
				{
					$rowset[$medal_id]['medal_issue'] = $medal_time . $this->user->format_date($row['time']) . $awarder_name . '</td></tr><tr><td>' . $medal_reason . '<div class="content">' . $reason . '</div><hr />';
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

			$data = [];

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

							$image = '<img src="' . $img_medals . '" alt="' . $medal_name . '" title="' . $medal_name . '" />' ;
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
							$image = '<img src="' . $data['image'] . '" alt="' . $medal_name . '" title="' . $medal_name . '" />';
							$small_image = $break . '<img src="' . $data['image'] . '" alt="' . $medal_name . '" title="' . $medal_name . '"' . $medal_width . $medal_height . ' />';
						}
					}
					else
					{
						$image = '<img src="' . $data['image'] . '" alt="' . $medal_name . '" title="' . $medal_name . '" />';
						$small_image = $break . '<img src="' . $data['image'] . '" alt="' . $medal_name . '" title="' . $medal_name . '"' . $medal_width . $medal_height . ' />';
					}

					$this->template->assign_block_vars('switch_display_medal.details', [
						'ISMEDAL_CAT' 		=> $newcat,
						'MEDAL_CAT' 		=> $data['cat_name'],
						'MEDAL_NAME' 		=> $medal_name,
						'MEDAL_DESCRIPTION' => $data['description'],
						'MEDAL_IMAGE' 		=> $image,
						'MEDAL_IMAGE_SMALL' => $small_image,
						'MEDAL_ISSUE' 		=> $data['medal_issue'],
						'MEDAL_COUNT' 		=> $this->user->lang['MEDAL_AMOUNT'] . ': ' . $data['medal_count'],
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
