<?php
/*
Plugin Name: League Table Importer for SportsPress
Plugin URI: #
Description: A Plugin which can import league tables in sportspress.
Author: Igor Benić
Author URI: http://twitter.com/igorbenic
Version: 0.2
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'League_Table_Importer_SportsPress' ) ) :

/**
 * Main SportsPress Soccer Class
 *
 * @class SportsPress_Soccer
 * @version	0.1
 */
class League_Table_Importer_SportsPress {
    

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    
    private $optionName = "ibenic_sportspress_import";
    private $pageSlug = "ibenic_sportspress_import";
	/**
	 * Constructor.
	 */
	public function __construct() {
		
		// Define constants
		$this->define_constants();
        
		add_action( 'tgmpa_register', array( $this, 'require_core' ) );

	

		// Include required files
		$this->includes();

		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        
	}

	 /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_submenu_page(
            'sportspress',
            'Import Tables', 
            'Import Tables', 
            'manage_options', 
           $this->pageSlug, 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
    	
        // Set class property
        $excelFile = get_option( $this->optionName );

        if(isset($_POST["ibenic_sportspress_submit"])){
        	//Upload Started
        	 
        		 

        		$upload_dir = wp_upload_dir();
	    		$upload_path = $upload_dir["basedir"]."/ibenic_sportspress_import/";
				$numFiles =  count($_FILES['ibenic_sportspress_excel']["tmp_name"]);

				if(!file_exists($upload_path)){
					mkdir($upload_path);
				}

			 

				
						
						
						$fileName = $_FILES["ibenic_sportspress_excel"]["name"];
						$fileNameChanged = str_replace(" ", "_", $fileName);

						$temp_name = $_FILES["ibenic_sportspress_excel"]["tmp_name"];
						//print_r($_FILES);
						$file_size = $_FILES["ibenic_sportspress_excel"]["size"];
						$fileError = $_FILES["ibenic_sportspress_excel"]["error"];
						$mb = 2 * 1024 * 1024;
						$targetPath = $upload_path;
						
						
						if($fileError > 0){
							
						    $this->show_message(__("There was an error", "sportspress-lti"), "error");

						} else {
			                 
								//DELETE if there is a file with the same name
								if(file_exists($targetPath."/".$fileNameChanged)){
									@unlink($targetPath."/".$fileNameChanged);
								}

								if($file_size <= $mb){

					            	if(move_uploaded_file($temp_name, $targetPath."/".$fileNameChanged)){
					            		
					            		@unlink($excelFile);

					            		update_option( $this->optionName, $targetPath."/".$fileNameChanged);
					            		
					            		$excelFile = get_option( $this->optionName );
					            	
					            	} else {
					            		
					            		$this->show_message(__("Upload Failed", "sportspress-lti"), "error");

					            	}
				            	
				           		 }  
				            	 
				             

				
			}

        	 

        }

        if(isset($_POST["ibenic_sportspress_save"])){

        	$recordAction = $_POST["record"];



        	//print_r($recordAction);

        	$columns = $_POST["ibenic_sportspress_column"];

        	//print_r($columns);

        	$the_team = "";

        	$league_table = array();

        	$error = false;

        	//Iterate over columns and get the infos
        	for($i = 0; $i < count($columns); $i++){

        		$resultRow = $columns[$i];

        		//Remove the first element from array and return as the value of the_team
        		$the_team = array_shift($resultRow);

        		$resultArray = array_combine($recordAction, $resultRow);

        		//print_r($resultArray);
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

        			$league_table[$the_team] = $resultArray;

        		} else {

        			$error = true;
        		}




        	}

        	if(!$error){
        		
        		if(isset($_POST["ibenic_sportspress_table"]) && $_POST["ibenic_sportspress_table"] != "0"){

        			update_post_meta($_POST["ibenic_sportspress_table"], 'sp_teams', $league_table);
        			//echo $_POST["ibenic_sportspress_table"];
        			//print_r($league_table);
        			update_post_meta($_POST["ibenic_sportspress_table"], 'sp_highlight', 0);
        			update_post_meta($_POST["ibenic_sportspress_table"], 'sp_select', 'manual');
        			update_post_meta($_POST["ibenic_sportspress_table"], 'sp_adjustments', array());
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
					$firstColumns = array_values($league_table);
					$firstColumns = $firstColumns[0];
					foreach ($firstColumns as $key => $value) {
						if(isset($columns[$key])){
							$theColumns[] = $key;
						}
					}
					update_post_meta($_POST["ibenic_sportspress_table"], 'sp_columns', $theColumns);
        			delete_post_meta($_POST["ibenic_sportspress_table"], 'sp_team');

        			$teamIDs = array_keys($league_table);
        			//print_r($teamIDs);
        			foreach ($teamIDs as $teamID) {
        				add_post_meta( $_POST["ibenic_sportspress_table"], 'sp_team', $teamID );
        			}

        		    $this->show_message(__("League Table imported successfully", "sportspress-lti"), "updated");


        		}elseif(isset($_POST["ibenic_sportspress_new_table"]) && $_POST["ibenic_sportspress_new_table"] != ""){

        			$newTable = wp_insert_post( array(
        					'post_title' => wp_strip_all_tags( $_POST["ibenic_sportspress_new_table"] ),
        					'post_content' => ' ',
        					'post_status' => 'publish',
        					'post_type' => 'sp_table'
        				), false );

        			update_post_meta($newTable, 'sp_teams', $league_table);

        			update_post_meta($newTable, 'sp_highlight', 0);
        			update_post_meta($newTable, 'sp_select', 'manual');
        			update_post_meta($newTable, 'sp_adjustments', array());
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
					$firstColumns = array_values($league_table);
					$firstColumns = $firstColumns[0];
					foreach ($firstColumns as $key => $value) {
						if(isset($columns[$key])){
							$theColumns[] = $key;
						}
					}
					update_post_meta($newTable, 'sp_columns', $theColumns);
        			delete_post_meta($newTable, 'sp_team');

        			$teamIDs = array_keys($league_table);
        			foreach ($teamIDs as $teamID) {
        				add_post_meta( $newTable, 'sp_team', $teamID );
        			}

        			$this->show_message(__("League Table imported successfully", "sportspress-lti"), "updated");

        		} else {

        			$this->show_message(__("You did not select a table to save data to or a new Table could not be created.", "sportspress-lti"), "error");
        		}



        	} else{

        		$this->show_message(__("There was an error reading the posted values from the table.", "sportspress-lti"), "error");

        	}



        }
      
        

        if($excelFile != null && $excelFile != ""){
        	require_once dirname( __FILE__ ) . '/includes/PHPExcel/PHPExcel.php';

        	//  Read your Excel workbook
			try {
			    $inputFileType = PHPExcel_IOFactory::identify($excelFile);
			    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
			    $objPHPExcel = $objReader->load($excelFile);
			} catch(Exception $e) {
			    die('Error loading file "'.pathinfo($excelFile,PATHINFO_BASENAME).'": '.$e->getMessage());
			}

			$dataArray = array();
			//  Get worksheet dimensions
			$sheet = $objPHPExcel->getSheet(0); 
			$highestRow = $sheet->getHighestRow(); 
			$highestColumn = $sheet->getHighestColumn();
			$dataArray = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow,
			                                    NULL,
			                                    TRUE,
			                                    FALSE);
			 

			$numberOfArrays = count($dataArray);
         
        $args = array(
			'post_type' => 'sp_column',
			'numberposts' => -1,
			'posts_per_page' => -1,
	  		'orderby' => 'menu_order',
	  		'order' => 'ASC'
		);
		$stats = get_posts( $args );

		$teamArgs = array(
			'post_type'=>'sp_team',
			'numberposts' => -1,
			'posts_per_page' => -1,
			'orderby' => 'menu_order',
	  		'order' => 'ASC'
			);
		$teams = get_posts($teamArgs);

		$teamsArray = array();

		foreach ($teams as $team) {
			$teamsArray[$team->ID] = $team->post_title;
		}

		$columns = array();
		$columns["pos"] = __("Position", "sportspress-lti");
		$columns["name"] = __("Team", "sportspress-lti");
		 
		foreach ( $stats as $stat ):

			 
			// Add column name to columns
			$columns[ $stat->post_name ] = $stat->post_title;
 
		endforeach;
		
		echo '<div class="wrap">';
		echo '<h2>'.__('Import Tables','sportspress-lti').'</h2>';
		echo '<h3>'.__('Imported Table','sportspress-lti').': '.$excelFile.'</h3>';
		echo ' <form method="post" action="" >';
			//get columns from the first
           $numberOfColumns = count($dataArray[0]);

           echo "<table class='widefat table'>";
           echo "<tr>";
          	 echo "<th>Action</th>";
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

          
			for($i = 0; $i < $numberOfArrays; $i++){

				 
				 $array = $dataArray[$i];
				 echo "<tr>";
				  echo "<td>";

				  echo "<select name='ibenic_sportspress_column[".$i."][0]'>";
				  	echo "<option value='0'>".__('Select a team', 'sportspress-lti')."</option>";
				  	echo "<option value='add'>Add as New team</option>";
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

        ?>
			<p class="description"><?php  _e('Leave it at "Select a table" if you want to enter a new table', 'sportspress-lti'); ?></p>
        	<h3><?php  _e('Add a new table', 'sportspress-lti'); ?></h3>
        	<p><input type="text" class="widefat" name="ibenic_sportspress_new_table" value="" placeholder="<?php  _e('Enter a new table', 'sportspress-lti'); ?>" />
			
			</p>
             	<button type="submit" name="ibenic_sportspress_save" class="button-primary"><?php  _e('Save Table', 'sportspress-lti'); ?></button>
            </form>
		

            <h3><?php  _e('Import another Table', 'sportspress-lti'); ?></h3>           
            <form method="post" action="" enctype="multipart/form-data">
             	<input type="file" name="ibenic_sportspress_excel" />
             	<button type="submit" name="ibenic_sportspress_submit" class="button-primary"><?php  _e('Upload Excel', 'sportspress-lti'); ?></button>
            </form>

            <p><?php _e("Table Example:", "sportspress-lti"); ?></p>
            <a href="<?php echo plugin_dir_url(__FILE__); ?>demo/table.xlsx"><?php _e("Table.xlsx", "sportspress-lti"); ?></a>
        </div>
        

        <?php


        }else{ 

        ?>


        <div class="wrap">
            <form method="post" action="" enctype="multipart/form-data">
             	<input type="file" name="ibenic_sportspress_excel" />
             	<button type="submit" name="ibenic_sportspress_submit" class="button-primary"><?php  _e('Upload Excel', 'sportspress-lti'); ?></button>
            </form>
                <p><?php _e("Table Example:", "sportspress-lti"); ?></p>
            <a href="<?php echo plugin_dir_url(__FILE__); ?>demo/table.xlsx"><?php _e("Table.xlsx", "sportspress-lti"); ?></a>
        
        </div>
        <?php

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
	 * Define constants.
	*/
	private function define_constants() {
		if ( !defined( 'IBENIC_SP_TABLE_VERSION' ) )
			define( 'IBENIC_SP_TABLE_VERSION', '0.1' );

		if ( !defined( 'IBENIC_SP_TABLE_URL' ) )
			define( 'IBENIC_SP_TABLE_URL', plugin_dir_url( __FILE__ ) );

		if ( !defined( 'IBENIC_SP_TABLE_DIR' ) )
			define( 'IBENIC_SP_TABLE_DIR', plugin_dir_path( __FILE__ ) );
	}

	

	/**
	 * Include required files.
	*/
	private function includes() {
		require_once dirname( __FILE__ ) . '/includes/class-tgm-plugin-activation.php';

	}

	/**
	 * Require SportsPress core.
	*/
	public static function require_core() {
		$plugins = array(
			array(
				'name'        => 'SportsPress',
				'slug'        => 'sportspress',
				'required'    => true,
				'is_callable' => array( 'SportsPress', 'instance' ),
			),
		);

		$config = array(
			'default_path' => '',
			'menu'         => 'tgmpa-install-plugins',
			'has_notices'  => true,
			'dismissable'  => true,
			'is_automatic' => true,
			'message'      => '',
			'strings'      => array(
				'nag_type' => 'updated'
			)
		);

		tgmpa( $plugins, $config );
	}

	

	
}

endif;

$League_Table_Importer_SportsPress = new League_Table_Importer_SportsPress();
