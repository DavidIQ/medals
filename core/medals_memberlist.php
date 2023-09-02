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
		$u_nominate = $u_award = '';
		if ($this->auth->acl_get('u_nominate_medals') && $user_id != $this->user->data['user_id'])
		{
			$u_nominate = $this->helper->route('bb3mobi_medals_controller', ['m' => 'nominate', 'u' => $user_id]);
		}
		if ($this->auth->acl_get('a_user') || $this->user->data['user_type'] == USER_FOUNDER || $this->auth->acl_get('u_award_medals'))
		{
			$u_award = $this->helper->route('bb3mobi_medals_controller', ['m' => 'award', 'u' => $user_id]);
		}
		$this->template->assign_vars([
			'U_NOMINATE_MEDALS'	=> $u_nominate,
			'U_AWARD_MEDALS'	=> $u_award,
		]);

		$sql_array = [
			'SELECT'	=> 'm.id, m.name, m.image, m.device, m.dynamic, ma.time, COUNT(m.id) AS medal_count',
			'FROM'			=> [ $this->tb_medals_awarded => 'ma' ],
			'LEFT_JOIN'	=> [
				[
					'FROM'	=> [  $this->tb_medals => 'm' ],
					'ON'	=> 'm.id = ma.medal_id',
				],
			],
			'WHERE'			=> "ma.user_id = {$user_id} AND ma.nominated = 0",
			'GROUP_BY'		=> 'm.id',
			'ORDER_BY'		=> 'ma.time DESC, m.order_id',
		];
		$sql = $this->db->sql_build_query('SELECT_DISTINCT', $sql_array);

		$result = $this->db->sql_query($sql);
		$user_awards_rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);
				
		if (!count($user_awards_rows))
		{
			return;
		}
		
		$medal_count = count($user_awards_rows);
		if ($medal_count)
		{
			$this->template->assign_vars([
				'USER_MEDAL_COUNT'	=> $medal_count,
				'MEDAL_LAST_MEDALS'	=> sprintf($this->user->lang['MEDAL_LAST_MEDALS'], (int) $this->config['medal_profile_display']),
				'U_MEDALS_VIEW'		=> $this->helper->route('bb3mobi_medals_awardsage', ['user_id' => $user_id])
			]);
		}

		$numberofmedals = 0;
		foreach ($user_awards_rows as $row)
		{
			$medal_name = $row['name'];
			if ($numberofmedals == (int) $this->config['medal_profile_display'])
			{
				break;
			}

			$numberofmedals++;
			$image_src = generate_board_url() . $this->config['medals_images_path'] . $row['image'];

			if ($row['medal_count'] > 1)
			{
				if ($row['dynamic'])
				{
					$image_src = $this->helper->route('bb3mobi_medals_controller', [
							'm' => 'mi',
							'med' => $image_src,
							'd' => generate_board_url() . $this->config['medals_images_path'] . 'devices/' . $row['device'] . '-' . ($row['medal_count'] - 1) . '.gif'
						]
					);
				}
				else
				{
					$cluster = '-' . $row['medal_count'];
					$device_image = substr_replace($image_src, $cluster, -4) . substr($image_src, -4);
					if (file_exists($device_image))
					{
						$image_src = $device_image;
					}
				}
			}

			$this->template->assign_block_vars('switch_display_medal_images', [
				'MEDAL_IMG_SRC'		=> $image_src,
				'MEDAL_IMG_NAME'	=> $medal_name,
				'MEDAL_IMG_WIDTH'	=> $this->config['medal_small_img_width'],
				'MEDAL_IMG_HEIGHT'	=> $this->config['medal_small_img_ht']
			]);
		}
	}
}
