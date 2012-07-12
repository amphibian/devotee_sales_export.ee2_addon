<?php
    $this->EE =& get_instance();

	echo form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=devotee_sales_export'.AMP.'method=save');
	
	$this->EE->table->set_template($cp_pad_table_template);
	
	$this->EE->table->set_heading(
		array('data'=> $this->EE->lang->line('setting')),
		array('data' => $this->EE->lang->line('value'))
	);
	
	$this->EE->table->add_row(
		form_label($this->EE->lang->line('devotee_sales_export_api'), 'api_key'),
		form_input('api_key', $api_key, 'id="api_key"')
	);		

	$this->EE->table->add_row(
		form_label($this->EE->lang->line('devotee_sales_export_secret'), 'secret_key'),
		form_input('secret_key', $secret_key, 'id="secret_key"')
	);		

	echo $this->EE->table->generate();	

	echo form_submit(
		array(
			'name' => 'submit',
			'value' => $this->EE->lang->line('devotee_sales_export_save_credentials'),
			'class' => 'submit'
		)
	);
	echo form_close();
?>