<?php

class LTI_Options_Xml extends LTI_Options {

	/**
	 * XML Node Name of each Team
	 * @var string
	 */
	public $xml_node_name = "";

	/**
	 * Option name to save XML Node Name
	 * @var string
	 */
	public $xml_node_name_option = "lti_xml_node";

	public function __construct(){

		$this->optionName = "lti_xml";

		$this->upload_file_name = "ibenic_sportspress_xml";

		$this->upload_text = __("Upload Xml", 'sportspress-lti');

		parent::__construct('Xml');

		add_action("lti_upload_form_" . $this->option_slug, array( $this, 'additional_upload_inputs' ) );

		add_action("lti_upload_form_save_" . $this->option_slug, array( $this, 'save_additional_upload_inputs' ) );

		$this->xml_node_name = get_option( $this->xml_node_name_option, "team" );
	}

	public function additional_upload_inputs(){
		?>
		<p>
			<label for="lti_xml_node_name"><?php _e( "Team Node Name:", "sportspress-lti");?></label>
			<input type="text" id="lti_xml_node_name" name="lti_xml_node_name" placeholder="<?php _e("Insert the node name of team", "sportspress-lti"); ?>" />
			<span class="description"><?php _e( "If the node name is not inserted, the node 'team' will be used", "sportspress-lti" ); ?></span>
		</p>
		<?php
	}

	public function save_additional_upload_inputs(){

		if( isset( $_POST["lti_xml_node_name"] ) && $_POST["lti_xml_node_name"] != "" ){

			update_option( $this->xml_node_name_option, $_POST["lti_xml_node_name"] );

		} else {

			update_option( $this->xml_node_name_option, "team" );

		}

	}



	public function read_file(){

		 

		//  Read your Excel workbook
		try {

			$excelFile = $this->optionData;
			
			$xml = new DOMDocument();
			$xml->load( $excelFile );
			//$xml = simplexml_load_file($excelFile);

		    
		    $teams = array();

		    foreach ( $xml->getElementsByTagName( $this->xml_node_name ) as $team  ){
		    	$team_array = $this->getArray( $team );
		    	$new_team_array = array();
		    	
		    	foreach ($team_array as $key => $value) {
		    		$new_team_array[] = trim($value[0]["#text"]);
		    	}

		    	$teams[] = $new_team_array;
		    }

		  
			$this->imported_data = $teams;
		
		} catch(Exception $e) {

		    die('Error loading file "'.pathinfo($excelFile,PATHINFO_BASENAME).'": '.$e->getMessage());

		}

		 

	}

	public function getArray($node)
	{
	    $array = false;

	    if ($node->hasAttributes())
	    {
	        foreach ($node->attributes as $attr)
	        {
	            $array[$attr->nodeName] = $attr->nodeValue;
	        }
	    }

	    if ($node->hasChildNodes())
	    {
	        if ($node->childNodes->length == 1)
	        {
	            $array[$node->firstChild->nodeName] = $node->firstChild->nodeValue;
	        }
	        else
	        {
	            foreach ($node->childNodes as $childNode)
	            {
	                if ($childNode->nodeType != XML_TEXT_NODE)
	                {
	                    $array[$childNode->nodeName][] = $this->getArray($childNode);
	                }
	            }
	        }
	    }

	    return $array;
	} 

	

	public function custom_layout(){
		?>
		
		<p><?php _e("Table Example:", "sportspress-lti"); ?></p>
        
        <a href="<?php echo IBENIC_SP_TABLE_URL; ?>/demo/table.xml"><?php _e("Table.xml", "sportspress-lti"); ?></a>
        <br/>
        <a href="<?php echo IBENIC_SP_TABLE_URL; ?>/demo/table2.xml"><?php _e("Table2.xml", "sportspress-lti"); ?></a>
        
		<?php
	}

	

	

}

add_filter( 'sportspress-lti_options', 'lti_options_xml_register', 10, 1 ); 
function lti_options_xml_register( $registered_options ){

		$registered_options[] = 'LTI_Options_Xml';

		return $registered_options;
}