<?php

    $this->EE =& get_instance();
	$this->EE->load->library('table');
    
    $this->EE->cp->add_js_script(array('ui' => array('datepicker')));
	$this->EE->cp->load_package_js('js');

	echo form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=devotee_sales_export'.AMP.'method=export');
	
	$this->EE->table->set_template($cp_pad_table_template);
	
	$this->EE->table->set_heading(
		array('data'=> $this->EE->lang->line('devotee_sales_export_addon_name')),
		array('data'=> $this->EE->lang->line('devotee_sales_export_start_date')),
		array('data' => $this->EE->lang->line('devotee_sales_export_end_date'))
	);
	
	$this->EE->table->add_row(
		form_dropdown('addon', $addons),
		form_input('start_date', null, 'id="start_date"'),
		form_input('end_date', null, 'id="end_date"')
	);			

	echo $this->EE->table->generate();	

	echo form_submit(
		array(
			'name' => 'submit',
			'value' => $this->EE->lang->line('devotee_sales_export_export'),
			'class' => 'submit'
		)
	);
	echo form_close();
?>