<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * devot:ee Sales Export Module Install/Update File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Derek Hogue
 * @link		http://amphibian.info
 */

class Devotee_sales_export_upd {
	
	public $version = '1.0';

	private $EE;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	

	public function install()
	{
		$mod_data = array(
			'module_name'			=> 'Devotee_sales_export',
			'module_version'		=> $this->version,
			'has_cp_backend'		=> "y",
			'has_publish_fields'	=> 'n'
		);
		
		$this->EE->db->insert('modules', $mod_data);
		
		$this->EE->load->dbforge();
		$this->EE->dbforge->add_field(array(
			'site_id' => array('type' => 'int', 'constraint' => '5', 'unsigned' => TRUE, 'null' => FALSE),
			'api_key' => array('type' => 'varchar', 'constraint' => '255'),
			'secret_key' => array('type' => 'varchar', 'constraint' => '255')
		));
		$this->EE->dbforge->add_key(array('site_id'), TRUE);
		$this->EE->dbforge->create_table('devotee_sales_export');			
		return TRUE;
	}


	public function uninstall()
	{
		$mod_id = $this->EE->db->select('module_id')
								->get_where('modules', array(
									'module_name'	=> 'Devotee_sales_export'
								))->row('module_id');
		
		$this->EE->db->where('module_id', $mod_id)
					 ->delete('module_member_groups');
		
		$this->EE->db->where('module_name', 'Devotee_sales_export')
					 ->delete('modules');
		
		$this->EE->load->dbforge();
		$this->EE->dbforge->drop_table('devotee_sales_export');
		
		return TRUE;
	}
	

	public function update($current = '')
	{
		return TRUE;
	}
	
}