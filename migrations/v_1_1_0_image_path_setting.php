<?php
/**
*
* @author Gremlinn (Nathan DuPra) mods@dupra.net | Anvar Stybaev (DEV Extension phpBB3.1.x)
* @package Medals System Extension
* @copyright Anvar 2015 (c) Extensions bb3.mobi
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace bb3mobi\medals\migrations;

class v_1_1_0_image_path_setting extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['medals_images_path']) && version_compare($this->config['medals_version'], '1.1.0', '>=');
	}

	static public function depends_on()
	{
		return ['\bb3mobi\medals\migrations\v_1_0_1'];
	}

	public function update_data()
	{
		return [
			// Add config
			['config.add', ['medals_images_path', '/images/medals/']],
			// Update version
			['config.update', ['medals_version', '1.1.0']],
		];
	}
}
