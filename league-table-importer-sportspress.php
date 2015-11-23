<?php
/*
Plugin Name: League Table Importer for SportsPress
Plugin URI: #
Description: A Plugin which can import league tables in sportspress.
Author: Igor Benić
Author URI: http://twitter.com/igorbenic
Version: 1.0
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
    	echo '<div class="wrap">';

    	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general'; 

    	$options = apply_filters( 'sportspress-lti_options', array( ) ); 

    	$tabs = array();

    	$activeClass = null;

    	foreach ( $options as $class ) {
    		
    		$theClass = new $class();

    		$tabs = $theClass->add_tab( $tabs );

    		/**
    		 * Ako je tab od te onda mi daj tu klasu!
    		 */
    		if( $active_tab == $theClass->option_slug ){

    			$activeClass = $theClass;

    		}

    	}

    	/**
    	 * If no tab is selected get the first one
    	 */
    	if($activeClass == null && count($options) > 0){

    		$class = $options[0];

    		$activeClass = new $class();

    		$active_tab = $activeClass->option_slug;
    	}
       	
       	/**
       	 * If there is still no active class, then show nothing 
       	 */
       	if($activeClass == null){

       		return;
       	}

    	?>
		

		 <h2 class="nav-tab-wrapper">  
			<?php foreach ( $tabs as $key => $value ): ?>
				<a href="?page=<?php echo $this->pageSlug; ?>&tab=<?php echo $key; ?>" class="nav-tab <?php echo $active_tab == $key ? 'nav-tab-active' : ''; ?>"><?php echo $value; ?></a>  
			<?php endforeach ?>
         </h2> 

    	<?php	
        
        $activeClass->init();

        if( isset( $_POST[ $activeClass->upload_id ] ) ) {

        	$activeClass->upload();

        }

        if( isset( $_POST[ $activeClass->save_id ] ) ){

        	$activeClass->save();

        }

        
        $activeClass->generate_forms();
 

        

        echo '</div>';
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
		require_once dirname( __FILE__ ) . '/includes/class-options.php';
		require_once dirname( __FILE__ ) . '/includes/class-options-excel.php';

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
