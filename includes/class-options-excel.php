<?php

class LTI_Options_Excel extends LTI_Options {

	

	public function __construct(){

		$this->optionName = "lti_excel";

		$this->upload_file_name = "ibenic_sportspress_excel";

		$this->upload_text = __("Upload Excel", 'sportspress-lti');

		parent::__construct('Excel');

	}

	public function read_file(){

		require_once dirname( __FILE__ ) . '/PHPExcel/PHPExcel.php';

		//  Read your Excel workbook
		try {

			$excelFile = $this->optionData;

		    $inputFileType = PHPExcel_IOFactory::identify($excelFile);

		    $objReader = PHPExcel_IOFactory::createReader($inputFileType);

		    $objPHPExcel = $objReader->load($excelFile);


		    $sheet = $objPHPExcel->getSheet(0); 

			$highestRow = $sheet->getHighestRow(); 

			$highestColumn = $sheet->getHighestColumn();

			$this->imported_data = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow,
			                                    NULL,
			                                    TRUE,
			                                    FALSE);
		
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

add_filter( 'sportspress-lti_options', 'lti_options_excel_register', 10, 1 ); 
function lti_options_excel_register( $registered_options ){

		$registered_options[] = 'LTI_Options_Excel';

		return $registered_options;
}