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
 * devot:ee Sales Export Module Control Panel File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Derek Hogue
 * @link		http://amphibian.info
 */

class Devotee_sales_export_mcp {
	
	public $site_id;
	private $_base_url;

	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->_base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=devotee_sales_export';
		$this->site_id = $this->EE->config->item('site_id');
		
		$this->EE->cp->set_right_nav(array(
			'devotee_sales_export_export'	=> $this->_base_url,
			'devotee_sales_export_credentials'	=> $this->_base_url.AMP.'method=credentials',
		));
	}


	public function index()
	{
		$this->EE->cp->set_variable('cp_page_title', lang('devotee_sales_export_module_name'));
		if( ! $credentials = $this->_get_credentials() )
		{
			$this->EE->functions->redirect($this->_base_url.AMP.'method=credentials');
			exit();
		}
		else
		{
			$vars = array('addons' => array('' => lang('devotee_sales_export_all_addons')));
			if($data = $this->_get_sales($credentials))
			{
				if(is_array($data['items']))
				{
					foreach($data['items'][0] as $item)
					{
						if(array_search($item['title'], $vars['addons']) === FALSE)
						{
							$vars['addons'][$item['title']] = $item['title'];
						}
					}
					asort($vars['addons']);
				}
			}
			return $this->EE->load->view('index', $vars, TRUE);
		}
	}


	public function credentials()
	{
		$this->EE->cp->set_variable('cp_page_title', lang('devotee_sales_export_credentials'));
		if( ! $vars = $this->_get_credentials() )
		{
			$vars = array(
				'api_key' => '',
				'secret_key' => ''
			);
		}
		return $this->EE->load->view('credentials', $vars, TRUE);

	}


	public function export()
	{
		if($credentials = $this->_get_credentials())
		{
			if($data = $this->_get_sales($credentials))			
			{
				$headers = array();
				if(is_array($data['items']))
				{
					foreach(array_keys($data['items'][0][0]) as $header)
					{
						$headers[] = $header;
						if($header == 'price')
						{
							$headers[] = 'net';
						}
					}
					
					$items = array();
					foreach($data['items'][0] as $index => $item)
					{
						$new_item = array();
						$addon = $this->EE->input->post('addon');
								
						foreach($item as $k => $v)
						{
							if( (!empty($addon) && $addon == $item['title']) || empty($addon) )
							{							
								if($k == 'purchase_date')
								{
									$new_item['purchase_date'] = $this->EE->localize->set_human_time($item['purchase_date']);
								}
								elseif($k == 'price')
								{
									$new_item['price'] = $item['price'];
									$new_item['net'] = number_format($item['price'] * 0.8, 2);
								}
								else
								{
									$new_item[$k] = $v;
								}
							}
						}
						$items[] = $new_item;							
					}
					
					$title = 'devotee-sales-export';
					
					$start_date = $this->EE->input->post('start_date');
					if(!empty($start_date))
					{
						$title .= '-'.$start_date;
					}
					else
					{
						$title .= '-'.$this->EE->localize->decode_date('%Y-%m-%d', $this->EE->localize->now - 31556926, FALSE);
					}
					
					$end_date = $this->EE->input->post('end_date');
					if(!empty($end_date))
					{
						$title .= '-'.$end_date;
					}
					else
					{
						$title .= '-'.$this->EE->localize->decode_date('%Y-%m-%d', $this->EE->localize->now, FALSE);
					}
					$title .= '.csv';
					
					require(PATH_THIRD.'devotee_sales_export/libraries/parsecsv.lib.php');
					$csv = new parseCSV();
					$csv->output($title, $items, $headers);
					exit();
				}
				else
				{
					$this->EE->output->show_user_error('general', lang('devotee_sales_export_no_sales'));
				}
			}
			else
			{
				$this->EE->output->show_user_error('general', lang('devotee_sales_export_no_connection'));
			}
		}
		else
		{
			$this->EE->functions->redirect($this->_base_url.AMP.'method=credentials');
			exit();
		}
	}
	
	
	public function save()
	{	
		$settings = array(
			'api_key' => trim($this->EE->input->post('api_key')),
			'secret_key' => trim($this->EE->input->post('secret_key'))
		);
		// Do we already have settings?
		$existing = $this->EE->db->query('SELECT site_id FROM exp_devotee_sales_export WHERE site_id = '.$this->site_id);
		if($existing->num_rows() > 0)
		{
			$this->EE->db->query(
				$this->EE->db->update_string('exp_devotee_sales_export', $settings, 'site_id = '.$this->site_id)
			);
		}
		else
		{
			// Create a new row
			$settings['site_id'] = $this->site_id;
			$this->EE->db->query(
				$this->EE->db->insert_string('exp_devotee_sales_export', $settings)
			);		
		}
		$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('preferences_updated'));			
		$this->EE->functions->redirect($this->_base_url);
		exit();
	}
	
	
	private function _get_credentials()
	{
		$credentials = $this->EE->db->query("
			SELECT api_key, secret_key 
			FROM exp_devotee_sales_export 
			WHERE site_id = ".$this->site_id
		);
		if($credentials->num_rows() > 0)
		{
			if( $credentials->row('api_key') == '' || $credentials->row('secret_key') == '' )
			{
				return FALSE;
			}
			else
			{
				return array(
					'api_key' => $credentials->row('api_key'),
					'secret_key' => $credentials->row('secret_key'),				
				);				
			}
		}
		else
		{
			return FALSE;
		}
	}
	
	
	private function _get_sales($credentials = FALSE)
	{
		if(is_array($credentials))
		{
			$post_string = 'api_key='.$credentials['api_key'].'&secret_key='.$credentials['secret_key'];		
			if($start_date = $this->EE->input->post('start_date'))
			{
				$post_string .= "&start_dt=".$start_date;
			}
			if($end_date = $this->EE->input->post('end_date'))
			{
				$post_string .= "&end_dt=".$end_date;
			}
			
			$ch = curl_init('https://devot-ee.com/api/orders');
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
			$response = urldecode(curl_exec($ch));
			if(!function_exists('json_decode'))
			{
				$this->EE->load->library('Services_json');
			}
			return json_decode($response, TRUE);	
		}
	}	

	
}