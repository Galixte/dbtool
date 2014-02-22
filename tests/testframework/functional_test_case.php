<?php
/**
*
* @package testing
* @copyright (c) 2014 Matt Friedman
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/*
* Note: Copied and inspired by test code written by nickvergessen and marc1706
*/

namespace vse\dbtool\tests\testframework;

abstract class functional_test_case extends \phpbb_functional_test_case
{
	protected $dbtool_enabled = false;

	/**
	* Enable the extension in the ACP Extensions area
	*
	* @access public
	*/
	public function enable_dbtool_ext()
	{
		$enable_dbtool = false;

		if ($this->dbtool_enabled === true || $this->check_if_enabled())
		{
			return;
		}

		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&sid=' . $this->sid);
		$disabled_extensions = $crawler->filter('tr.ext_disabled')->extract(array('_text'));
		foreach ($disabled_extensions as $extension)
		{
			if (strpos($extension, 'Database Optimize') !== false)
			{
				$enable_dbtool = true;
			}
		}

		if ($enable_dbtool)
		{
			$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=enable_pre&ext_name=vse%2fdbtool&sid=' . $this->sid);
			$form = $crawler->selectButton('Enable')->form();
			$crawler = self::submit($form);
			$this->assertContains('The extension was enabled successfully', $crawler->text());
			$this->dbtool_enabled = true;
			$this->set_enabled();
		}
	}

	/**
	* Check if the extension is enabled
	*
	* @access protected
	*/
	protected function check_if_enabled()
	{
		$this->db = $this->get_db();

		$sql = "SELECT config_value FROM phpbb_config WHERE config_name = 'dbtool_ext_enabled'";
		$result = $this->db->sql_query($sql);
		$enabled = $this->db->sql_fetchfield('config_value');
		$this->db->sql_freeresult($result);

		return $enabled;
	}

	/**
	* Set the extension to enabled
	*
	* @access protected
	*/
	protected function set_enabled()
	{
		$this->db = $this->get_db();

		$sql = "INSERT INTO phpbb_config (config_name, config_value, is_dynamic) VALUES ('dbtool_ext_enabled', 1, 1)";
		$this->db->sql_query($sql);
	}
}
