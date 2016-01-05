<?php

class LTI_Options_Xml extends LTI_Options {

	

	public function __construct(){

		$this->optionName = "lti_xml";

		$this->upload_file_name = "ibenic_sportspress_xml";

		$this->upload_text = __("Upload Xml", 'sportspress-lti');

		parent::__construct('Xml');

	}

	public function read_file(){

		 

		//  Read your Excel workbook
		try {

			$excelFile = $this->optionData;
			
			$xml = simplexml_load_file($excelFile);
		    
		    	print_r($xml);

			$this->imported_data = array();
		
		} catch(Exception $e) {

		    die('Error loading file "'.pathinfo($excelFile,PATHINFO_BASENAME).'": '.$e->getMessage());

		}

		 

	}

	public function custom_layout(){
		?>
		
		<p><?php _e("Table Example:", "sportspress-lti"); ?></p>
        
        <a href="<?php echo plugin_dir_url(__FILE__); ?>demo/table.xlsx"><?php _e("Table.xlsx", "sportspress-lti"); ?></a>
        
		<?php
	}

	

	

}

add_filter( 'sportspress-lti_options', 'lti_options_xml_register', 10, 1 ); 
function lti_options_xml_register( $registered_options ){

		$registered_options[] = 'LTI_Options_Xml';

		return $registered_options;
}