<?php

/**
 * 
 */
abstract class LTI_Options {

	/**
	 * Option slug used for tab and other naming functions
	 * @var string
	 */
	public $option_slug = "";

	/**
	 * Title of option
	 * @var string
	 */
	public $title = "";

	/**
	 * Upload ID used for submit when uploading
	 * @var string
	 */
	public $upload_id = "";

	/**
	 * Save ID used for submit when saving the imported data
	 * @var string
	 */
	public $save_id = "";

	/**
	 * Upload Directory to store files
	 * @var string
	 */
	public $upload_dir = "";

	/**
	 * Option Name used to store mixed content of the upload
	 * @var string
	 */
	public $optionName = "";

	/**
	 * Option Data that is retrieved from database
	 * @var mix (string, array)
	 */
	public $optionData = "";

	/**
	 * Max File Size in MB
	 * @var integer
	 */
	public $maxFileSize = 2;

	/**
	 * Form Field definitions
	 * @var array
	 */
	public $fields = array();

	/**
	 * League Table ID to which we save data
	 * @var integer
	 */
	public $league_table_id = 0;

	/**
	 * Holds the posted record information
	 * @var array
	 */
	public $recordAction = array();

	/**
	 * Holds the posted columns information
	 * @var array
	 */
	public $columns = array();

	/**
	 * League Table Data 
	 * @var array
	 */
	public $league_table_data = array();

	/**
	 * Upload Button Text
	 * @var string
	 */
	public $upload_text = "";

	/**
	 * Input Name for the File Input
	 * @var string
	 */
	public $upload_file_name = "";

	/**
	 * Imported Data 
	 * @var array
	 */
	public $imported_data = array();

	/**
	 * File Upload errors
	 * @var array
	 */
	protected $file_errors = array();

	/**
	 * Delete if there is a file with the same name
	 * @var boolean
	 */
	protected $delete_same_name = true;

	/**
	 * Remove previous uploaded file
	 * @var boolean
	 */
	protected $remove_previous_file = true;

	public function __construct ( $name ) {

		$this->option_slug = sanitize_title( $name );

		$this->title = $name;

		$this->upload_id = $this->option_slug. "_upload";

		$this->save_id = $this->option_slug."_save";

		

	}

	public function setFileErrors(){

	    $this->file_errors = array(
			0 => __( 'There is no error, the file uploaded with success', 'sportspress-lti' ),
		    1 => __( 'The uploaded file exceeds the upload_max_filesize directive in php.ini', 'sportspress-lti' ),
		    2 => __( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form', 'sportspress-lti' ),
		    3 => __( 'The uploaded file was only partially uploaded', 'sportspress-lti' ),
		    4 => __( 'No file was uploaded', 'sportspress-lti' ),
		    6 => __( 'Missing a temporary folder', 'sportspress-lti' ),
		    7 => __( 'Failed to write file to disk.', 'sportspress-lti' ),
		    8 => __( 'A PHP extension stopped the file upload.', 'sportspress-lti' ),
		);

	}

	/**
	 * Get file size in bytes
	 * @return integer
	 */
	public function getFileSize(){

		return $this->maxFileSize * 1024 * 1024;

	}

	/**
	 * Adding to array the slug and the title of this option.
	 * @param array $registered_options
	 * @return array
	 */
	public function add_tab( $registered_options ){

		$registered_options[ $this->option_slug ] = $this->title;

		return $registered_options;

	}

	/**
	 * Getting the upload dir
	 * @return string
	 */
	public function get_upload_dir(){

		return $this->upload_dir;

	}

	/**
	 * Setting other properties when the class is active
	 * @return void
	 */
	public function init(){

		$upload_dir = wp_upload_dir();

		$this->setFileErrors();

		$this->upload_dir = $upload_dir["basedir"]. "/lti_" . $this->option_slug . "/";

		$this->optionData = get_option( $this->optionName );

		$this->fields = $this->form_fields();

	}

	/**
	 * Uploading the file. First checking if the required directory exists and if not creates it
	 * After that we call the function uploadFile that is customized by each import option
	 * @return [type] [description]
	 */
	public function upload(){

		$this->createIfNotExists( $this->upload_dir );


		$this->uploadFile();

	}

	/**
	 * Uploading File
	 * @return void
	 */
	public function uploadFile(){

		$numFiles =  count($_FILES[ $this->upload_file_name ]["tmp_name"]);
		
		$fileName = $_FILES[ $this->upload_file_name ]["name"];
		
		$fileNameChanged = str_replace(" ", "_", $fileName);;

		$temporaryName = $_FILES[ $this->upload_file_name ]["tmp_name"];
						
		$fileSize = $_FILES[ $this->upload_file_name ]["size"];

		$fileError = $_FILES[ $this->upload_file_name ]["error"];

		$this->get_file_uploaded( $fileName, $temporaryName, $fileSize, $fileError );

	}

	/**
	 * Gets the file uploaded
	 * @param  string $fileName      
	 * @param  string $temporaryName 
	 * @param  integer $fileSize      
	 * @param  integer $fileError     
	 * @return void                
	 */
	public function get_file_uploaded( $fileName, $temporaryName, $fileSize, $fileError ){

		if( $this->isFileError( $fileError ) ){

			$this->show_message( sprintf( __("There was an error: %s", "sportspress-lti"), $this->getFileError( $fileError ) ), "error");

		} else {

			$whereToUpload = $this->upload_dir . "/" . $fileName;

	        if(file_exists( $whereToUpload )){
					
				if( $this->delete_same_name ){

					@unlink( $whereToUpload );
					
				} else{

					//Make FileName an array
					$fileNameArray = explode( ".", $fileName );

					//Get the filename from the array and concatenate with time
					$fileName = $fileNameArray[0] . time(); 

					//Remove the filename from the array
					unset($fileNameArray[0]);

					//Concatenate filename with the rest of the array with a fullstop '.'
					$fileName = $fileName . implode( ".", $fileNameArray );

					$whereToUpload = $this->upload_dir . "/" . $fileName;
				}
			}

			if( $fileSize > $this->getFileSize() ){

				$this->show_message(sprintf( __("File is too large, maximum file size is %d MB", "sportspress-lti"), $this->getFileSize() ), "error");

			} else {

				if( move_uploaded_file( $temporaryName, $whereToUpload ) ){
					

					if( $this->remove_previous_file ):	            		
		        		
		        		/**
		        		 * @since 0.3
		        		 */
		        		if( $this->optionData != $whereToUpload ){
		        			@unlink( $this->optionData );
		        		}

	        		endif;
	        		

	        		update_option( $this->optionName, $whereToUpload );

	        		do_action("lti_upload_form_save_" . $this->option_slug);
	        		
	        		$this->optionData = get_option( $this->optionName );

	        	} else {
	        		
	        		$this->show_message( __("Upload Failed", "sportspress-lti"), "error" );

	        	}

			}

		}
		

	}

	/**
     * Show a WordPress Admin Notice
     * @param  string $msg  Message to show
     * @param  string $type Type of message that is used as a class
     * @return string      
     */
    private function show_message($msg, $type){

    	echo"<div class=\"$type\"> <p>$msg</p></div>";

    }


    /**
     * Check if there is an error with the file
     * @param  integer  $error 
     * @return boolean        
     */
	public function isFileError( $error ){

		if($error > 0) return true;

		return false;

	}

	/**
	 * Gets the file error description
	 * @param  integer $error 
	 * @return string        
	 */
	public function getFileError( $error ){

		return $this->file_errors( $error );

	}


	/**
	 * Create the directory if needed
	 * @param  string $upload_dir 
	 * @return void             
	 */
	public function createIfNotExists( $upload_dir ){


		if(!file_exists( $upload_dir )){
			
			mkdir( $upload_dir );
		
		}

	}

	/**
	 * Generates the form for saving the data
	 * @return void
	 */
	public function generate_save_form(){

    ?>  
        <form method="post" action="" >
        <?php

			$this->generate_table();

			$this->generate_form_fields();

			$this->custom_form_fields();

			$this->generate_league_tables_select();
			?>

			<p class="description"><?php  _e('Leave it at "Select a table" if you want to enter a new table', 'sportspress-lti'); ?></p>
        	
        	<h3><?php  _e('Add a new table', 'sportspress-lti'); ?></h3>
        	
        	<p>

        		<input type="text" class="widefat" name="ibenic_sportspress_new_table" value="" placeholder="<?php  _e('Enter a new table', 'sportspress-lti'); ?>" />
			
			</p>
             
             <button type="submit" name="<?php echo $this->save_id; ?>" class="button-primary"><?php  _e('Save Table', 'sportspress-lti'); ?></button>
            
		 </form>
	<?php

	}

	/**
	 * Generating imported table
	 * @return void
	 */
	public function generate_table(){

		/**
		 * Reading the uploaded file and filling the structured array
		 */
		$this->read_file();

		/**
		 * Getting the imported data as array
		 * @var array
		 */
		$dataArray = $this->imported_data;

		/**
		 * Do not generate table if there is no data
		 */
		if( count( $dataArray ) == 0) return;

		/**
		 * Number of rows
		 * @var integer
		 */
		$numberOfRows = count($dataArray);
        

        /**
         * Arguments to get SportsPress configuration set
         * @var array
         */
        $args = array(
			'post_type' => 'sp_column',
			'numberposts' => -1,
			'posts_per_page' => -1,
	  		'orderby' => 'menu_order',
	  		'order' => 'ASC'
		);
		/**
		 * Getting all SportsPress Configuration
		 * @var array
		 */
		$stats = get_posts( $args );

		/**
		 * Arguments to get all SportsPress Teams
		 * @var array
		 */
		$teamArgs = array(
			'post_type'=>'sp_team',
			'numberposts' => -1,
			'posts_per_page' => -1,
			'orderby' => 'menu_order',
	  		'order' => 'ASC'
			);
		/**
		 * SportsPress Teams
		 * @var array
		 */
		$teams = get_posts($teamArgs);

		/**
		 * Array to use for generating select for each row
		 * @var array
		 */
		$teamsArray = array();

		/**
		 * Filling the array for generating select
		 */
		foreach ($teams as $team) {

			$teamsArray[$team->ID] = $team->post_title;

		}

		/**
		 * Columns used to generate select for each column
		 * @var array
		 */
		$columns = array();

		$columns["pos"] = __("Position", "sportspress-lti");

		$columns["name"] = __("Team", "sportspress-lti");
		 
		foreach ( $stats as $stat ):

			// Add column name to columns
			$columns[ $stat->post_name ] = $stat->post_title;
 
		endforeach;

		/**
		 * Using the first row of data to count all the columns
		 * @var integer
		 */
		$numberOfColumns = count( $dataArray[ 0 ] );

        echo "<table class='widefat table'>";
           echo "<tr>";
          		echo "<th>" . __( 'Action', 'sportspress-lti' ) . "</th>";
          	 
      	 		for($j = 0; $j < $numberOfColumns; $j++){
          	
          			echo "<th>";

          				echo "<select name='record[]'>";

          	 				foreach ($columns as $key => $value) {
          	 					echo "<option value='".$key."'>".$value."</option>";
          	 				}

          	 			echo "</select>";

          	 		echo "</th>";
      	 		}
           echo "</tr>";

          
			for($i = 0; $i < $numberOfRows; $i++){

				 
				$array = $dataArray[$i];
				
				echo "<tr>";
				  
				  	echo "<td>";

				  		echo "<select name='ibenic_sportspress_column[".$i."][0]'>";
				  			
				  			echo "<option value='0'>" . __( 'Select a team', 'sportspress-lti' ) . "</option>";
				  			
				  			echo "<option value='add'>" . __( 'Add as a new Team', 'sportspress-lti' ) . "</option>";
	          	 			
	          	 			foreach ($teamsArray as $key => $value) {
	          	 				
	          	 				echo "<option value='".$key."'>".$value."</option>";
	          	 			
	          	 			}

	          	 		echo "</select>";

	          	 	echo "</td>";

	          	 	//Goes from 1 because the action is the 0
	          	 	$dataCounter = 1;
				 	
				 	foreach ($array as $data) {
				 	
				 		echo "<td>".$data."<input type='hidden' name='ibenic_sportspress_column[".$i."][".$dataCounter."]' value='".$data."' /></td>";
				 	$dataCounter++;
				 	}
				echo "</tr>";



			}
		echo "</table>";


	}

	/**
	 * Function to return form fields
	 * @return array
	 */
	public function form_fields(){

		return array();

	}

	/**
	 * Generate all league tables in a dropdown
	 * @return void
	 */
	public function generate_league_tables_select(){

		$tableArgs = array(
			'post_type'=>'sp_table',
			'numberposts' => -1,
			'posts_per_page' => -1,
			'orderby' => 'menu_order',
	  		'order' => 'ASC'
		);
		
		$table = get_posts($tableArgs);
		
		$tableArray = array();
		
		foreach ($table as $table_item) {
			
			$tableArray[$table_item->ID] = $table_item->post_title;

		}

		echo '<p><select name="ibenic_sportspress_table">';

			echo '<option value="0">Select a Table</option>';

				foreach ($tableArray as $key => $value) {

					echo '<option value="'.$key.'">'.$value.'</option>';

				}

		echo "</select></p>";

	}

	/**
	 * Generate all forms
	 * @return [type] [description]
	 */
	public function generate_forms(){

		$theFile = $this->optionData;

		if( $theFile != null && $theFile != "" && file_exists($theFile) ) {

			$this->generate_save_form();

		}

		$this->upload_form();

		$this->custom_layout();
	}

	/**
	 * Reads the file. Generating table
	 * @return void
	 */
	public function read_file(){


	}

	/**
	 * Generates form fields
	 * @return void 
	 */
	public function generate_form_fields(){


	}

	/**
	 * Generate custom form fields
	 * @return void
	 */
	public function custom_form_fields(){


	}

	/**
	 * Generates the upload form
	 * @return void
	 */
	public function upload_form(){

		?>

			<h3><?php  _e('Import Table', 'sportspress-lti'); ?></h3>           
            <form method="post" action="" enctype="multipart/form-data">
             	<input type="file" name="<?php echo $this->upload_file_name; ?>" />
             	<?php do_action("lti_upload_form_" . $this->option_slug); ?>
             	<button type="submit" name="<?php echo $this->upload_id;  ?>" class="button-primary"><?php echo $this->upload_text; ?></button>
           		

            </form>

		<?php

	}

	/**
	 * Saving the imported data
	 * @return void
	 */
	public function save(){

		$this->recordAction = $_POST["record"];

		$this->columns = $_POST["ibenic_sportspress_column"];

		$the_team = "";

    	 
    	$error = false;

    	//Iterate over columns and get the infos
    	for($i = 0; $i < count($this->columns); $i++){

    		$resultRow = $this->columns[$i];

    		//Remove the first element from array and return as the value of the_team
    		$the_team = array_shift($resultRow);

    		$resultArray = array_combine($this->recordAction, $resultRow);

    	 
    		if($the_team == "add"){
    			
    			$the_team_name = $resultArray["name"];

    			$the_team = wp_insert_post( array(
    					'post_title' => wp_strip_all_tags( $the_team_name ),
    					'post_content' => ' ',
    					'post_status' => 'publish',
    					'post_type' => 'sp_team'

    				), false );

    		}

    		if($the_team != 0){

    			$this->league_table_data[$the_team] = $resultArray;

    		} else {

    			$error = true;
    		}




    	}

    	if(!$error){
    		
    		if(isset($_POST["ibenic_sportspress_table"]) && $_POST["ibenic_sportspress_table"] != "0"){

    			$this->league_table_id = $_POST["ibenic_sportspress_table"];

    			$this->save_table();
 

    		}elseif(isset($_POST["ibenic_sportspress_new_table"]) && $_POST["ibenic_sportspress_new_table"] != ""){

    			$newTable = wp_insert_post( array(
    					'post_title' => wp_strip_all_tags( $_POST["ibenic_sportspress_new_table"] ),
    					'post_content' => ' ',
    					'post_status' => 'publish',
    					'post_type' => 'sp_table'
    				), false );

    			$this->league_table_id = $newTable;

    			$this->save_table();

    		} else {

    			$this->show_message(__("You did not select a table to save data to or a new Table could not be created.", "sportspress-lti"), "error");
    		}



    	} else{

    		$this->show_message(__("There was an error reading the posted values from the table.", "sportspress-lti"), "error");

    	}


	}

	public function save_table(){

		update_post_meta( $this->league_table_id, 'sp_teams', $this->league_table_data );
		 
		update_post_meta($this->league_table_id, 'sp_highlight', 0);
		update_post_meta($this->league_table_id, 'sp_select', 'manual');
		update_post_meta($this->league_table_id, 'sp_adjustments', array());
		$args = array(
			'post_type' => 'sp_column',
			'numberposts' => -1,
			'posts_per_page' => -1,
	  		'orderby' => 'menu_order',
	  		'order' => 'ASC'
		);
		$stats = get_posts( $args );

		$columns = array();
		foreach ( $stats as $stat ):

 
			// Add column name to columns
			$columns[ $stat->post_name ] = $stat->post_title;
 
		endforeach;

		$theColumns = array();

		$firstColumns = array_values($this->league_table_data);

		$firstColumns = $firstColumns[0];

		foreach ($firstColumns as $key => $value) {
			if(isset($columns[$key])){
				$theColumns[] = $key;
			}
		}
		update_post_meta($this->league_table_id, 'sp_columns', $theColumns);
		delete_post_meta($this->league_table_id, 'sp_team');

		$teamIDs = array_keys($this->league_table_data);
		 
		foreach ($teamIDs as $teamID) {
			add_post_meta( $this->league_table_id, 'sp_team', $teamID );
		}

		do_action("lti_save_table_" . $this->option_slug);

		$this->show_message(__("League Table Imported Successfully.", "sportspress-lti"), "updated");

	    
	}



}