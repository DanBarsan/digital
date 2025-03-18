<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
Plugin Name:        Spice Starter Sites
Plugin URI:         https://olivewp.org/
Description:        The plugin allows you to create professional designed pixel perfect websites in minutes. Import the stater sites to create the beautiful websites.
Version:            1.3
Requires at least:  5.3
Requires PHP:       5.2
Tested up to:       6.6.2
Author:             spicethemes
Author URI:         https://spicethemes.com
License:            GPLv2 or later
License URI:        http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain:        spice-starter-sites
Domain Path:        /languages
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
        die;
}
define('SPICE_STARTER_SITES_PLUGIN_PATH',trailingslashit(plugin_dir_path(__FILE__)));
define('SPICE_STARTER_SITES_PLUGIN_URL',trailingslashit(plugins_url('/',__FILE__)));
define('SPICE_STARTER_SITES_PLUGIN_UPLOAD',trailingslashit( wp_upload_dir()['basedir'] ) );

// Assuming SPICE_STARTER_SITES_VERSION is defined somewhere in your plugin
if ( ! defined( 'SSSP_VERSION' ) ) {
    define( 'SSSP_VERSION', '1.3' );
}

/**
 * Set up and initialize
 */
$theme=wp_get_theme();
class Spice_Starter_Sites {
        private static $instance;

        /**
         * Actions setup
         */
        public function __construct() {
            add_action( 'plugins_loaded', array( $this, 'constants' ), 2 );
            add_action( 'plugins_loaded', array( $this, 'includes' ), 4 );
            add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
            $theme=wp_get_theme();             
             if($theme->name !='Newscrunch' && 'Newscrunch Child' != $theme->name && 'Newscrunch child' != $theme->name && $theme->name !='NewsBlogger')
             {
                //check if the One Click Demo Importer is installed or activated
                if(!class_exists('OCDI_Plugin')) 
                {
                    add_action('admin_notices', array( $this, 'admin_notice' ), 6 );
                     return;
                }
            }
        }

        /**
         * Constants
        */
        function constants() {
            define( 'Spice_Starter_Sites_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
        }

        /**
         * Includes
         */
        function includes() {
            $theme=wp_get_theme();
            if($theme->name =='OliveWP' || 'OliveWP Child' == $theme->name || 'OliveWP child' == $theme->name) {
                if(! function_exists( 'spice_starter_sites_plus_plugin' ) ) {
                    require_once( Spice_Starter_Sites_DIR . 'demo-content/setup.php' );
                }
            }
        }

        /*
        * Admin Notice
        * Warning when the site doesn't have One Click Demo Importer installed or activated    
        */
        public function admin_notice() {
            echo '<div class="notice notice-warning is-dismissible"><p>', esc_html__('"Spice Starter Sites" requires "One Click Demo Import" to be installed and activated.','spice-starter-sites'), '</p></div>';
        }

        static function install() {
            if ( version_compare(PHP_VERSION, '5.4', '<=') ) {
                wp_die( esc_html__( 'Spice Starter Sites requires PHP 5.4. Please contact your host to upgrade your PHP. The plugin was <strong>not</strong> activated.', 'spice-starter-sites' ) );
            };

        }

        /**
         * Returns the instance.
        */
        public static function get_instance() {

            if ( !self::$instance )
                self::$instance = new self;

            return self::$instance;
        }


        /**
         * Load the localisation file.
        */
        public function load_plugin_textdomain() {
            load_plugin_textdomain( 'spice-starter-sites' , false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }
}

function spice_starter_sites_plugin() {
    return Spice_Starter_Sites::get_instance();
}
add_action('plugins_loaded', 'spice_starter_sites_plugin', 1);

//Does not activate the plugin on PHP less than 5.4
register_activation_hook( __FILE__, array( 'Spice_Starter_Sites', 'install' ) );

//Add Style in About Page
add_action('admin_enqueue_scripts','spice_starter_sites_importer_style_script');
if(!function_exists('spice_starter_sites_importer_style_script')){
    function spice_starter_sites_importer_style_script(){
        $id = $GLOBALS['hook_suffix'];
        if('customize.php' !=$id){
            if('admin_page_spice-settings-importer'== $id || !empty($id) || 'admin_page_spice-settings-importer'== $id  ){
                wp_enqueue_style( 'spice-starter-sites-importer-about-css', SPICE_STARTER_SITES_PLUGIN_URL . 'assets/css/about.css', array(), SSSP_VERSION );
                if ( class_exists('Newscrunch_Plus') )
                {
                    wp_enqueue_script('newscrunch-plus-install', SPNCP_URL . 'inc/admin/assets/js/plugin-install.js', array('jquery'), SSSP_VERSION, true);   
                }
            }
        }
    }
}

//Define and declair global variable
global $spice_starter_sites_importer_filepath, $spice_starter_sites_importer_pro_filepath, $spice_starter_sites_importer_new_filepath;
if($theme->name =='Newscrunch' || 'Newscrunch Child' == $theme->name || 'Newscrunch child' == $theme->name){

    $demo_link='https://spicethemes.com/spice-newscrunch-importer/';

    $spice_starter_sites_importer_filepath= array(
       'newscrunch'=>array(
        'title'=>esc_html__('Newscrunch','spice-starter-sites'),
        'slug'=>'newscrunch',
        'content'=>$demo_link.'newscrunch/content.xml',
        'customizer'=>$demo_link.'newscrunch/customizer.dat',
        'widget'=>$demo_link.'newscrunch/widget.wie',
        'image'=>$demo_link.'newscrunch/newscrunch.jpg',
        'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-one/',
        'plugin'=>'wpcf7-wpseo',

       ),
       'politics'=>array(
        'title'=>esc_html__('Politics','spice-starter-sites'),
        'slug'=>'politics',
        'content'=>$demo_link.'politics/content.xml',
        'customizer'=>$demo_link.'politics/customizer.dat',
        'widget'=>$demo_link.'politics/widget.wie',
        'image'=>$demo_link.'politics/politics.jpg',
        'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-two/',
        'plugin'=>'wpcf7-wpseo-sps',
       ),
       'architec'=>array(
        'title'=>esc_html__('Architec','spice-starter-sites'),
        'slug'=>'architec',
        'content'=>$demo_link.'architec/content.xml',
        'customizer'=>$demo_link.'architec/customizer.dat',
        'widget'=>$demo_link.'architec/widget.wie',
        'image'=>$demo_link.'architec/architec.jpg',
        'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-three/',
        'plugin'=>'wpcf7-wpseo',
       ),
    );
    $spice_starter_sites_importer_pro_filepath= array(
       'newscrunch-pro'=>array(
        'title'=>esc_html__('Newscrunch Pro','spice-starter-sites'),
        'slug'=>'newscrunch-pro',
        'content'=>$demo_link.'newscrunch-pro/content.xml',
        'customizer'=>$demo_link.'newscrunch-pro/customizer.dat',
        'widget'=>$demo_link.'newscrunch-pro/widget.wie',
        'image'=>$demo_link.'newscrunch-pro/newscrunch-pro.jpg',
        'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-one/',
        'plugin'=>'wpcf7-wpseo-wpmap',
       ),
       'restaurant-pro'=>array(
        'title'=>esc_html__('Restaurant','spice-starter-sites'),
        'slug'=>'restaurant-pro',
        'content'=>$demo_link.'restaurant-pro/content.xml',
        'customizer'=>$demo_link.'restaurant-pro/customizer.dat',
        'widget'=>$demo_link.'restaurant-pro/widget.wie',
        'image'=>$demo_link.'restaurant-pro/restaurant-pro.jpg',
        'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-two/',
        'plugin'=>'wpcf7-wpseo-wpmap-ssp',
       ),
       'technology-pro'=>array(
        'title'=>esc_html__('Technology','spice-starter-sites'),
        'slug'=>'technology-pro',
        'content'=>$demo_link.'technology-pro/content.xml',
        'customizer'=>$demo_link.'technology-pro/customizer.dat',
        'widget'=>$demo_link.'technology-pro/widget.wie',
        'image'=>$demo_link.'technology-pro/technology-pro.jpg',
        'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-three/',
        'plugin'=>'wpcf7-wpseo-wpmap-ssp',
       ),
       'true-gamers-pro'=>array(
        'title'=>esc_html__('True Gamers','spice-starter-sites'),
        'slug'=>'true-gamers-pro',
        'content'=>$demo_link.'true-gamers-pro/content.xml',
        'customizer'=>$demo_link.'true-gamers-pro/customizer.dat',
        'widget'=>$demo_link.'true-gamers-pro/widget.wie',
        'image'=>$demo_link.'true-gamers-pro/true-gamers-pro.jpg',
        'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-four/',
        'plugin'=>'wpcf7-wpseo-wpmap',
       ),
       'busi-crunch-pro'=>array(
        'title'=>esc_html__('Busi Crunch','spice-starter-sites'),
        'slug'=>'busi-crunch-pro',
        'content'=>$demo_link.'busi-crunch-pro/content.xml',
        'customizer'=>$demo_link.'busi-crunch-pro/customizer.dat',
        'widget'=>$demo_link.'busi-crunch-pro/widget.wie',
        'image'=>$demo_link.'busi-crunch-pro/busi-crunch-pro.jpg',
        'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-five/',
        'plugin'=>'wpcf7-wpseo-wpmap-ssp',
       ),
        'fashion-world-pro'=>array(
        'title'=>esc_html__('Fashion World','spice-starter-sites'),
        'slug'=>'fashion-world-pro',
        'content'=>$demo_link.'fashion-world-pro/content.xml',
        'customizer'=>$demo_link.'fashion-world-pro/customizer.dat',
        'widget'=>$demo_link.'fashion-world-pro/widget.wie',
        'image'=>$demo_link.'fashion-world-pro/fashion-world-pro.jpg',
        'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-six/',
        'plugin'=>'wpcf7-wpseo-wpmap-ssp',
       ),
       'life-style-pro'=>array(
        'title'=>esc_html__('Life Style','spice-starter-sites'),
        'slug'=>'life-style-pro',
        'content'=>$demo_link.'life-style-pro/content.xml',
        'customizer'=>$demo_link.'life-style-pro/customizer.dat',
        'widget'=>$demo_link.'life-style-pro/widget.wie',
        'image'=>$demo_link.'life-style-pro/life-style-pro.jpg',
        'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-seven/',
        'plugin'=>'wpcf7-wpseo-wpmap-ssp',
       ),  
       'digital-pro'=>array(
        'title'=>esc_html__('Digital','spice-starter-sites'),
        'slug'=>'digital-pro',
        'content'=>$demo_link.'digital-pro/content.xml',
        'customizer'=>$demo_link.'digital-pro/customizer.dat',
        'widget'=>$demo_link.'digital-pro/widget.wie',
        'image'=>$demo_link.'digital-pro/digital-pro.jpg',
        'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-eight/',
        'plugin'=>'wpcf7-wpseo-wpmap',
       ),
       'pet-care-pro'=>array(
        'title'=>esc_html__('Pet Care','spice-starter-sites'),
        'slug'=>'pet-care-pro',
        'content'=>$demo_link.'pet-care-pro/content.xml',
        'customizer'=>$demo_link.'pet-care-pro/customizer.dat',
        'widget'=>$demo_link.'pet-care-pro/widget.wie',
        'image'=>$demo_link.'pet-care-pro/pet-care-pro.jpg',
        'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-nine/',
        'plugin'=>'wpcf7-wpseo-wpmap-ssp',
       ),
       'sports-mag-pro'=>array(
        'title'=>esc_html__('Sports Mag','spice-starter-sites'),
        'slug'=>'sports-mag-pro',
        'content'=>$demo_link.'sports-mag-pro/content.xml',
        'customizer'=>$demo_link.'sports-mag-pro/customizer.dat',
        'widget'=>$demo_link.'sports-mag-pro/widget.wie',
        'image'=>$demo_link.'sports-mag-pro/sports-mag-pro.jpg',
        'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-ten/',
        'plugin'=>'wpcf7-wpseo-wpmap-ssp',
       ),
        'fitness-club-pro'=>array(
        'title'=>esc_html__('Fitness Club','spice-starter-sites'),
        'slug'=>'fitness-club-pro',
        'content'=>$demo_link.'fitness-club-pro/content.xml',
        'customizer'=>$demo_link.'fitness-club-pro/customizer.dat',
        'widget'=>$demo_link.'fitness-club-pro/widget.wie',
        'image'=>$demo_link.'fitness-club-pro/fitness-club-pro.jpg',
        'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-eleven/',
        'plugin'=>'wpcf7-wpseo-wpmap-ssp',
       ),
        'photography-pro'=>array(
        'title'=>esc_html__('Photography','spice-starter-sites'),
        'slug'=>'photography-pro',
        'content'=>$demo_link.'photography-pro/content.xml',
        'customizer'=>$demo_link.'photography-pro/customizer.dat',
        'widget'=>$demo_link.'photography-pro/widget.wie',
        'image'=>$demo_link.'photography-pro/photography-pro.jpg',
        'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-twelve/',
        'plugin'=>'wpcf7-wpseo-wpmap',
       ),
        'news-cloud-pro'=>array(
        'title'=>esc_html__('News Cloud','spice-starter-sites'),
        'slug'=>'news-cloud-pro',
        'content'=>$demo_link.'newscloud-pro/content.xml',
        'customizer'=>$demo_link.'newscloud-pro/customizer.dat',
        'widget'=>$demo_link.'newscloud-pro/widget.wie',
        'image'=>$demo_link.'newscloud-pro/newscloud-pro.jpg',
        'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-thirteen/',
        'plugin'=>'wpcf7-wpseo-wpmap',
       ),
        'consulting-pro'=>array(
        'title'=>esc_html__('Consulting','spice-starter-sites'),
        'slug'=>'consulting-pro',
        'content'=>$demo_link.'consulting-pro/content.xml',
        'customizer'=>$demo_link.'consulting-pro/customizer.dat',
        'widget'=>$demo_link.'consulting-pro/widget.wie',
        'image'=>$demo_link.'consulting-pro/consulting-pro.jpg',
        'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-fourteen/',
        'plugin'=>'wpcf7-wpseo-wpmap',
       ),
        'trip-travel-pro'=>array(
        'title'=>esc_html__('Trip Travel','spice-starter-sites'),
        'slug'=>'trip-travel-pro',
        'content'=>$demo_link.'trip-travel-pro/content.xml',
        'customizer'=>$demo_link.'trip-travel-pro/customizer.dat',
        'widget'=>$demo_link.'trip-travel-pro/widget.wie',
        'image'=>$demo_link.'trip-travel-pro/trip-travel-pro.jpg',
        'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-fifteen/',
        'plugin'=>'wpcf7-wpseo-wpmap',
       ),
    );
}

if('NewsBlogger' == $theme->name){

    $demo_link='https://spicethemes.com/spice-newscrunch-importer/';

    $spice_starter_sites_importer_filepath= array(
       'newsblogger'=>array(
        'title'=>esc_html__('Default','spice-starter-sites'),
        'slug'=>'newsblogger',
        'content'=>$demo_link.'newsblogger/content.xml',
        'customizer'=>$demo_link.'newsblogger/customizer.dat',
        'widget'=>$demo_link.'newsblogger/widget.wie',
        'image'=>$demo_link.'newsblogger/newsblogger.jpg',
        'demo_link'=>'https://demo-news.spicethemes.com/startersite-1/',
        'plugin'=>'wpcf7',

       ),
       'travel'=>array(
        'title'=>esc_html__('Travel','spice-starter-sites'),
        'slug'=>'travel',
        'content'=>$demo_link.'travel/content.xml',
        'customizer'=>$demo_link.'travel/customizer.dat',
        'widget'=>$demo_link.'travel/widget.wie',
        'image'=>$demo_link.'travel/travel.jpg',
        'demo_link'=>'https://demo-news.spicethemes.com/startersite-2/',
        'plugin'=>'wpcf7',
       ),
       'business-news'=>array(
        'title'=>esc_html__('Business News','spice-starter-sites'),
        'slug'=>'business-news',
        'content'=>$demo_link.'business-news/content.xml',
        'customizer'=>$demo_link.'business-news/customizer.dat',
        'widget'=>$demo_link.'business-news/widget.wie',
        'image'=>$demo_link.'business-news/business-news.jpg',
        'demo_link'=>'https://demo-news.spicethemes.com/startersite-3/',
        'plugin'=>'wpcf7',
       ),
    );
    $spice_starter_sites_importer_pro_filepath= array(
       'newsblogger-pro'=>array(
        'title'=>esc_html__('Default Pro','spice-starter-sites'),
        'slug'=>'newsblogger-pro',
        'content'=>$demo_link.'newsblogger-pro/content.xml',
        'customizer'=>$demo_link.'newsblogger-pro/customizer.dat',
        'widget'=>$demo_link.'newsblogger-pro/widget.wie',
        'image'=>$demo_link.'newsblogger-pro/newsblogger-pro.jpg',
        'demo_link'=>'https://demo-news.spicethemes.com/pro-startersite-1/',
        'plugin'=>'wpcf7-wpmap',
       ),
       'creative-pro'=>array(
        'title'=>esc_html__('Creative','spice-starter-sites'),
        'slug'=>'creative-pro',
        'content'=>$demo_link.'creative-pro/content.xml',
        'customizer'=>$demo_link.'creative-pro/customizer.dat',
        'widget'=>$demo_link.'creative-pro/widget.wie',
        'image'=>$demo_link.'creative-pro/creative-pro.jpg',
        'demo_link'=>'https://demo-news.spicethemes.com/pro-startersite-2/',
        'plugin'=>'wpcf7-wpmap',
       ),
       'healthcare-pro'=>array(
        'title'=>esc_html__('Healthcare','spice-starter-sites'),
        'slug'=>'healthcare-pro',
        'content'=>$demo_link.'healthcare-pro/content.xml',
        'customizer'=>$demo_link.'healthcare-pro/customizer.dat',
        'widget'=>$demo_link.'healthcare-pro/widget.wie',
        'image'=>$demo_link.'healthcare-pro/healthcare-pro.jpg',
        'demo_link'=>'https://demo-news.spicethemes.com/pro-startersite-3/',
        'plugin'=>'wpcf7-wpmap',
       ),   
       'newsblogger-arabic-pro-rtl'=>array(
        'title'=>esc_html__('NewsBlogger Arabic','spice-starter-sites'),
        'slug'=>'newsblogger-arabic-pro-rtl',
        'content'=>$demo_link.'newsblogger-arabic-pro-rtl/content.xml',
        'customizer'=>$demo_link.'newsblogger-arabic-pro-rtl/customizer.dat',
        'widget'=>$demo_link.'newsblogger-arabic-pro-rtl/widget.wie',
        'image'=>$demo_link.'newsblogger-arabic-pro-rtl/newsblogger-arabic-pro-rtl.jpg',
        'demo_link'=>'https://demo-news.spicethemes.com/pro-startersite-5/',
        'plugin'=>'wpcf7-wpmap',
       ),     
       'commercial-pro-rtl'=>array(
        'title'=>esc_html__('Commercial RTL','spice-starter-sites'),
        'slug'=>'commercial-pro-rtl',
        'content'=>$demo_link.'commercial-pro-rtl/content.xml',
        'customizer'=>$demo_link.'commercial-pro-rtl/customizer.dat',
        'widget'=>$demo_link.'commercial-pro-rtl/widget.wie',
        'image'=>$demo_link.'commercial-pro-rtl/commercial-pro-rtl.jpg',
        'demo_link'=>'https://demo-news.spicethemes.com/pro-startersite-6/',
        'plugin'=>'wpcf7-wpmap',
       ), 
    );
}

//Create options page
add_action( 'admin_menu', 'spice_starter_sites_importer_options_page',999 );
if(!function_exists('spice_starter_sites_importer_options_page')){
    function spice_starter_sites_importer_options_page() {
        $theme=wp_get_theme();
        if($theme->name =='Newscrunch' || 'Newscrunch Child' == $theme->name || 'Newscrunch child' == $theme->name) {
            if ( class_exists('Newscrunch_Plus') )
            {
                add_submenu_page(
                    'newscrunch-plus-welcome',
                    esc_html__( 'Demo Import', 'spice-starter-sites' ),
                    esc_html__( 'Demo Import', 'spice-starter-sites' ),
                    'manage_options',
                    'spice-starter-sites',
                    function() { require_once SPICE_STARTER_SITES_PLUGIN_PATH.'/admin/view.php'; },
                    20
                );
            }
            else
            {
                add_submenu_page(
                    'newscrunch-welcome',
                    esc_html__( 'Demo Import', 'spice-starter-sites' ),
                    esc_html__( 'Demo Import', 'spice-starter-sites' ),
                    'manage_options',
                    'spice-starter-sites',
                    function() { require_once SPICE_STARTER_SITES_PLUGIN_PATH.'/admin/view.php'; },
                    20
                );
            }
            
            add_submenu_page(
                'spice-starter-sites',
                esc_html__( 'Demo Import', 'spice-starter-sites' ),
                esc_html__( 'Demo Import', 'spice-starter-sites' ),
                'manage_options',
                'spice-settings-importer',
                function() { $dfg=new Spice_Starter_Sites_Demo_Import(); $dfg->display();},
                1
            );
        }  
        if('NewsBlogger' == $theme->name) {
            if ( class_exists('Newscrunch_Plus') )
            {
                add_submenu_page(
                    'newscrunch-plus-welcome',
                    esc_html__( 'Demo Import', 'spice-starter-sites' ),
                    esc_html__( 'Demo Import', 'spice-starter-sites' ),
                    'manage_options',
                    'spice-starter-sites',
                    function() { require_once SPICE_STARTER_SITES_PLUGIN_PATH.'/admin/view.php'; },
                    20
                );
            }
            else
            {
                add_submenu_page(
                    'newsblogger-welcome',
                    esc_html__( 'Demo Import', 'spice-starter-sites' ),
                    esc_html__( 'Demo Import', 'spice-starter-sites' ),
                    'manage_options',
                    'spice-starter-sites',
                    function() { require_once SPICE_STARTER_SITES_PLUGIN_PATH.'/admin/view.php'; },
                    20
                );
            }
            
            add_submenu_page(
                'spice-starter-sites',
                esc_html__( 'Demo Import', 'spice-starter-sites' ),
                esc_html__( 'Demo Import', 'spice-starter-sites' ),
                'manage_options',
                'spice-settings-importer',
                function() { $dfg=new Spice_Starter_Sites_Demo_Import(); $dfg->display();},
                1
            );
        }  
    }
}

function spice_starter_sites_importer_customizer_settings($path)
{
    // Check to see if the settings have already been imported.
    $template = get_template();
    $imported = get_option( $template . '_customizer_import', false );
    
    // Bail if already imported.
    if ( $imported ) {
        return;
    }    
    
    // Return if the file doesn't exist.
    if ( ! file_exists( $path ) ) {
        return;
    }
    
    // Get the settings data.
    $data = @unserialize( file_get_contents( $path ) );
    
    // Return if something is wrong with the data.
    if ( 'array' != gettype( $data ) || ! isset( $data['mods'] ) ) {
        return;
    }
    
    // Import options.
    if ( isset( $data['options'] ) ) {
        foreach ( $data['options'] as $option_key => $option_value ) {
            update_option( $option_key, $option_value );
        }
    }
    
    // Import mods.
    foreach ( $data['mods'] as $key => $val ) {
        set_theme_mod( $key, $val );
    }
    
    // Set the option so we know these have already been imported.
    update_option( $template . '_customizer_import', true );
}

function spice_starter_sites_importer_process_import_file($file){
    global $spice_starter_sites_importer_import_results;

    // File exists?
    if (! file_exists($file)) {
        wp_die(
            esc_html__('Import file could not be found. Please try again.', 'spice-starter-sites'),
            '',
            array(
                'back_link' => true,
            )
        );
    }

    // Get file contents and decode.
    $data = file_get_contents($file);
    $data = json_decode($data);
    // Delete import file.
    // unlink($file);

    // Import the widget data
    // Make results available for display on import/export page.
    $spice_starter_sites_importer_import_results = spice_starter_sites_importer_import_data($data);
}

function spice_starter_sites_importer_import_data($data){

    global $wp_registered_sidebars;

    if (empty($data) || ! is_object($data)) {
        wp_die(
            esc_html__('Import data is invalid.', 'spice-starter-sites'),
            '',
            array(
                'back_link' => true,
            )
        );
    }

    // Hook before import.
    do_action('spice_starter_sites_importer_before_import');
    $data = apply_filters('spice_starter_sites_importer_import_data', $data);

    // Get all available widgets site supports.
    $available_widgets = spice_starter_sites_importer_available_widgets();

    // Get all existing widget instances.
    $widget_instances = array();
    foreach ($available_widgets as $widget_data) {
        $widget_instances[$widget_data['id_base']] = get_option('widget_' . $widget_data['id_base']);
    }

    // Begin results.
    $results = array();

    // Loop import data's sidebars.
    foreach ($data as $sidebar_id => $widgets) {
        // Skip inactive widgets (should not be in export file).
        if ('wp_inactive_widgets' === $sidebar_id) {
            continue;
        }

        // Check if sidebar is available on this site.
        // Otherwise add widgets to inactive, and say so.
        if (isset($wp_registered_sidebars[$sidebar_id])) {
            $sidebar_available    = true;
            $use_sidebar_id       = $sidebar_id;
            $sidebar_message_type = 'success';
            $sidebar_message      = '';
        } else {
            $sidebar_available    = false;
            $use_sidebar_id       = 'wp_inactive_widgets'; // Add to inactive if sidebar does not exist in theme.
            $sidebar_message_type = esc_html__('error', 'spice-starter-sites');
            $sidebar_message      = esc_html__('Widget area does not exist in theme (using Inactive)', 'spice-starter-sites');
        }

        // Result for sidebar
        // Sidebar name if theme supports it; otherwise ID.
        $results[$sidebar_id]['name']         = ! empty($wp_registered_sidebars[$sidebar_id]['name']) ? $wp_registered_sidebars[$sidebar_id]['name'] : $sidebar_id;
        $results[$sidebar_id]['message_type'] = $sidebar_message_type;
        $results[$sidebar_id]['message']      = $sidebar_message;
        $results[$sidebar_id]['widgets']      = array();

        // Loop widgets.
        foreach ($widgets as $widget_instance_id => $widget) {
            $fail = false;

            // Get id_base (remove -# from end) and instance ID number.
            $id_base            = preg_replace('/-[0-9]+$/', '', $widget_instance_id);
            $instance_id_number = str_replace($id_base . '-', '', $widget_instance_id);

            // Does site support this widget?
            if (! $fail && ! isset($available_widgets[$id_base])) {
                $fail                = true;
                $widget_message_type = esc_html__('error','spice-starter-sites');
                $widget_message      = esc_html__('Site does not support widget', 'spice-starter-sites'); // Explain why widget not imported.
            }

            // Filter to modify settings object before conversion to array and import
            // Leave this filter here for backwards compatibility with manipulating objects (before conversion to array below)
            // Ideally the newer spice_starter_sites_importer_widget_settings_array below will be used instead of this.
            $widget = apply_filters('spice_starter_sites_importer_widget_settings', $widget);

            // Convert multidimensional objects to multidimensional arrays
            // Some plugins like Jetpack Widget Visibility store settings as multidimensional arrays
            // Without this, they are imported as objects and cause fatal error on Widgets page
            // If this creates problems for plugins that do actually intend settings in objects then may need to consider other approach: https://wordpress.org/support/topic/problem-with-array-of-arrays
            // It is probably much more likely that arrays are used than objects, however.
            $widget = json_decode(wp_json_encode($widget), true);

            // Filter to modify settings array
            // This is preferred over the older spice_starter_sites_importer_widget_settings filter above
            // Do before identical check because changes may make it identical to end result (such as URL replacements).
            $widget = apply_filters('spice_starter_sites_importer_widget_settings_array', $widget);

            // Does widget with identical settings already exist in same sidebar?
            if (! $fail && isset($widget_instances[$id_base])) {
                // Get existing widgets in this sidebar.
                $sidebars_widgets = get_option('sidebars_widgets');
                $sidebar_widgets  = isset($sidebars_widgets[$use_sidebar_id]) ? $sidebars_widgets[$use_sidebar_id] : array(); // Check Inactive if that's where will go.

                // Loop widgets with ID base.
                $single_widget_instances = ! empty($widget_instances[$id_base]) ? $widget_instances[$id_base] : array();
                foreach ($single_widget_instances as $check_id => $check_widget) {
                    // Is widget in same sidebar and has identical settings?
                    if (in_array("$id_base-$check_id", $sidebar_widgets, true) && (array) $widget === $check_widget) {
                        $fail                = true;
                        $widget_message_type = esc_html__('warning','spice-starter-sites');

                        // Explain why widget not imported.
                        $widget_message = esc_html__('Widget already exists', 'spice-starter-sites');

                        break;
                    }
                }
            }

            // No failure.
            if (! $fail) {
                // Add widget instance
                $single_widget_instances   = get_option('widget_' . $id_base); // All instances for that widget ID base, get fresh every time.
                $single_widget_instances   = ! empty($single_widget_instances) ? $single_widget_instances : array(
                    '_multiwidget' => 1,   // Start fresh if have to.
                );
                $single_widget_instances[] = $widget; // Add it.

                // Get the key it was given.
                end($single_widget_instances);
                $new_instance_id_number = key($single_widget_instances);

                // If key is 0, make it 1
                // When 0, an issue can occur where adding a widget causes data from other widget to load,
                // and the widget doesn't stick (reload wipes it).
                if ('0' === strval($new_instance_id_number)) {
                    $new_instance_id_number = 1;
                    $single_widget_instances[$new_instance_id_number] = $single_widget_instances[0];
                    unset($single_widget_instances[0]);
                }

                // Move _multiwidget to end of array for uniformity.
                if (isset($single_widget_instances['_multiwidget'])) {
                    $multiwidget = $single_widget_instances['_multiwidget'];
                    unset($single_widget_instances['_multiwidget']);
                    $single_widget_instances['_multiwidget'] = $multiwidget;
                }

                // Update option with new widget.
                update_option('widget_' . $id_base, $single_widget_instances);

                // Assign widget instance to sidebar.
                // Which sidebars have which widgets, get fresh every time.
                $sidebars_widgets = get_option('sidebars_widgets');

                // Avoid rarely fatal error when the option is an empty string
                // https://github.com/churchthemes/widget-importer-exporter/pull/11.
                if (! $sidebars_widgets) {
                    $sidebars_widgets = array();
                }

                // Use ID number from new widget instance.
                $new_instance_id = $id_base . '-' . $new_instance_id_number;

                // Add new instance to sidebar.
                $sidebars_widgets[$use_sidebar_id][] = $new_instance_id;

                // Save the amended data.
                update_option('sidebars_widgets', $sidebars_widgets);

                // After widget import action.
                $after_widget_import = array(
                    'sidebar'           => $use_sidebar_id,
                    'sidebar_old'       => $sidebar_id,
                    'widget'            => $widget,
                    'widget_type'       => $id_base,
                    'widget_id'         => $new_instance_id,
                    'widget_id_old'     => $widget_instance_id,
                    'widget_id_num'     => $new_instance_id_number,
                    'widget_id_num_old' => $instance_id_number,
                );
                do_action('spice_starter_sites_importer_after_widget_import', $after_widget_import);

                // Success message.
                if ($sidebar_available) {
                    $widget_message_type = esc_html__('success','spice-starter-sites');
                    $widget_message      = esc_html__('Imported', 'spice-starter-sites');
                } else {
                    $widget_message_type = esc_html__('warning','spice-starter-sites');
                    $widget_message      = esc_html__('Imported to Inactive', 'spice-starter-sites');
                }
            }

            // Result for widget instance
            $results[$sidebar_id]['widgets'][$widget_instance_id]['name']         = isset($available_widgets[$id_base]['name']) ? $available_widgets[$id_base]['name'] : $id_base;      // Widget name or ID if name not available (not supported by site).
            $results[$sidebar_id]['widgets'][$widget_instance_id]['title']        = ! empty($widget['title']) ? $widget['title'] : esc_html__('No Title', 'spice-starter-sites');  // Show "No Title" if widget instance is untitled.
            $results[$sidebar_id]['widgets'][$widget_instance_id]['message_type'] = $widget_message_type;
            $results[$sidebar_id]['widgets'][$widget_instance_id]['message']      = $widget_message;
        }
    }

    // Hook after import.
    do_action('spice_starter_sites_importer_after_import');

    // Return results.
    return apply_filters('spice_starter_sites_importer_import_results', $results);
}

function spice_starter_sites_importer_available_widgets()
{
    global $wp_registered_widget_controls;

    $widget_controls = $wp_registered_widget_controls;

    $available_widgets = array();

    foreach ($widget_controls as $widget) {
        // No duplicates.
        if (! empty( $widget['id_base'] ) && ! isset( $available_widgets[ $widget['id_base'] ] )) {
            $available_widgets[ $widget['id_base'] ]['id_base'] = $widget['id_base'];
            $available_widgets[ $widget['id_base'] ]['name']    = $widget['name'];
        }
    }

    return apply_filters( 'spice_starter_sites_importer_available_widgets', $available_widgets );
}

function spice_starter_sites_importer_set_after_import_mods() {

    $main_menu = get_term_by( 'name', 'Primary Menu', 'nav_menu' );
    $footer_menu = get_term_by( 'name', 'Footer Menu', 'nav_menu' );
    set_theme_mod( 'nav_menu_locations', array(
            'primary' => $main_menu->term_id,
            'footer_menu' => $footer_menu->term_id,
        )
    );
}

add_action( 'spice_starter_sites_importer_after', 'spice_starter_sites_importer_set_after_import_mods' );

class Spice_Starter_Sites_Demo_Import {
    public $dir;
    public $url;
    public $demo_args;
        
   function display( ){
        $show_export = false;
        wp_nonce_field( 'export_action', 'export_nonce' );
        if ( isset( $_REQUEST['export'] ) && $_REQUEST['export'] == 1 ) {
            // Check if the nonce is set and valid
            if ( isset( $_REQUEST['export_nonce'] ) ) {
                $export_nonce = sanitize_text_field( wp_unslash( $_REQUEST['export_nonce'] ) );
                if ( wp_verify_nonce( $export_nonce, 'export_action' ) ) {
                    // Nonce is valid, proceed with the export
                    $show_export = true;
                }
            } else {
                // Invalid nonce, reject the request or handle it as needed
                wp_die( 'Security check failed!' );
            }
        }?>            
        <div class="wrap spice-starter-sites-importer-dashboard" id="ertvg">
            <div class="theme_info info-tab-content">
            <h3 class="spice-starter-sites-importer-heading"><?php esc_html_e('Spice Starter Sites Importer','spice-starter-sites');?></h3>
                <div class="block-container" id="myDivs1">
                    <div class="block-row">
                        <div class="block-col-2">
                            <?php                             
                            $theme_name  = isset($_GET['theme']) ? sanitize_text_field(wp_unslash($_GET['theme'])) : '';
                            $theme_title = isset($_GET['title']) ? sanitize_text_field(wp_unslash($_GET['title'])) : '';
                            global $spice_starter_sites_importer_filepath, $spice_starter_sites_importer_pro_filepath;
                            if(!empty($spice_starter_sites_importer_filepath[$theme_name]['image'])){
                                echo '<img width="600" height="400" src="'.esc_url($spice_starter_sites_importer_filepath[$theme_name]['image']).'" />';
                            }
                            if(!empty($spice_starter_sites_importer_pro_filepath[$theme_name]['image'])){
                                echo '<img width="600" height="400" src="'.esc_url($spice_starter_sites_importer_pro_filepath[$theme_name]['image']).'" />';
                            }         
                            ?>
                        </div>
                        <div class="block-col-2" align="justify">
                        <div class="importer-header">
                            <?php // Translators: %s is the theme title. ?>
                            <h3><?php printf( esc_html__( 'Theme: %s', 'spice-starter-sites' ), esc_html( $theme_title ) ); ?></h3>
                        </div>
                        <div class="importer-body">
                            <?php 
                            $theme=wp_get_theme();
                            $themeslug=$theme->stylesheet;
                            $themeprefix=str_replace('-','_',$themeslug);
                            
                            if ( class_exists('Newscrunch_Plus') ):
                                $name='newscrunch_plus_about_page';
                            else: 
                                $name=$themeprefix.'_about_page';
                            endif;
                            global $$name; 
                            $news_crunch_actions = $$name->recommended_actions;
                            $news_crunch_actions_todo = get_option( 'recommended_actions', false );
                            $plugindata = isset($_GET['plugin']) ? sanitize_text_field(wp_unslash($_GET['plugin'])) : '';

                            $plugindata_arr = explode("-", $plugindata);
                            $newplugindata_arr=array();
                            $length=sizeof($plugindata_arr);
                            for($i=0;$i<$length;$i++){
                                if($plugindata_arr[$i]==='wpcf7'){
                                    array_push($newplugindata_arr,'install_contact-form-7');
                                }
                                else if($plugindata_arr[$i]==='wpseo'){
                                    array_push($newplugindata_arr,'install_wordpress-seo');
                                }
                                if($plugindata_arr[$i]==='sps'){
                                    array_push($newplugindata_arr,'install_spice-post-slider');
                                }
                                 if($plugindata_arr[$i]==='sss'){
                                    array_push($newplugindata_arr,'install_spice-social-share');
                                }
                                 if($plugindata_arr[$i]==='sseo'){
                                    array_push($newplugindata_arr,'install_seo-optimized-images');
                                }
                                if($plugindata_arr[$i]==='ssp'){
                                    array_push($newplugindata_arr,'install_spice-slider-pro');
                                }
                                if($plugindata_arr[$i]==='wpmap'){
                                    array_push($newplugindata_arr,'install_wp-google-maps');
                                }
                            }
                            ?>
                            <div id="recommended_actions" class="news-crunch-tab-pane">
                                <h4><?php esc_html_e( 'Recommended Plugins:','spice-starter-sites');?></h4>
                                <table>
                                    <?php 
                                    if($news_crunch_actions): 
                                        foreach ($news_crunch_actions as $key => $news_crunch_val): 
                                            for($i=0;$i<sizeof($newplugindata_arr);$i++){
                                                if($news_crunch_val['id']==$newplugindata_arr[$i]):?>
                                                    <tr>                                                        
                                                        <td class="<?php echo esc_attr($news_crunch_val['id']);?>">
                                                            <?php echo esc_html($news_crunch_val['title']); ?>
                                                        </td>
                                                        <td style=" float: right;">
                                                            <?php 
                                                            if(!$news_crunch_val['is_done']): 
                                                                echo wp_kses_post($news_crunch_val['link']); 
                                                            else:
                                                                echo '<span class="dashicons dashicons-yes"></span>';
                                                            endif;?>
                                                        </td>
                                                    </tr>                                                        
                                                    <?php 
                                                endif;
                                            }
                                        endforeach; 
                                    endif; ?>
                                </table>
                            </div> 
                        <?php ?>  
                        <?php
                    $sse_confirm=array();
                    for($i=0;$i<$length;$i++){
                        if($plugindata_arr[$i]==='wpcf7'){
                            if(class_exists('WPCF7')){array_push($sse_confirm,'true');}else{array_push($sse_confirm,'false');}
                        }
                        else if($plugindata_arr[$i]==='wpseo'){
                            if(function_exists('wpseo_init')){array_push($sse_confirm,'true');}else{array_push($sse_confirm,'false');}
                        }
                        if($plugindata_arr[$i]==='sps'){
                            if(class_exists('Spice_Post_Slider')){array_push($sse_confirm,'true');}else{array_push($sse_confirm,'false');}
                        }
                        if($plugindata_arr[$i]==='ssp'){
                            if(class_exists('Spice_Slider_Pro')){array_push($sse_confirm,'true');}else{array_push($sse_confirm,'false');}
                        }
                        if($plugindata_arr[$i]==='sss'){
                            if(class_exists('Spice_Social_Share')){array_push($sse_confirm,'true');}else{array_push($sse_confirm,'false');}
                        }
                        if($plugindata_arr[$i]==='sseo'){
                            if(function_exists('sobw_fs')){array_push($sse_confirm,'true');}else{array_push($sse_confirm,'false');}
                        }
                        if($plugindata_arr[$i]==='wpmap'){
                            if(function_exists('wpgmaps_init')){array_push($sse_confirm,'true');}else{array_push($sse_confirm,'false');}
                        }
                    }
                    $devi=0;
                    for($i=0;$i<sizeof($sse_confirm);$i++){
                        if($sse_confirm[$i]==='true'){
                            $devi++;
                        }

                    }?>                 
                    <div align="left" style="padding:20px auto"> 
                        <form action="" method="POST" id="myform">
                            <button type="submit" name="spice_starter_sites_importer_demo_import" data-theme="<?php echo esc_attr($theme_name);?>" class="spice-starter-sites-importer-button spice_starter_sites_importer_demo_import button-primary">
                            <?php
                            if( get_option( 'spice_starter_sites_importer_demo_imported' ) == 1 ) {
                                esc_html_e('Import Again', 'spice-starter-sites');
                            } else {
                                esc_html_e('Import Demo Data', 'spice-starter-sites');
                            }
                            ?>
                            </button>
                            <a href="<?php echo esc_url('admin.php?page=spice-starter-sites');?>" class="spice-starter-sites-importer-button button-primary" ><?php esc_html_e( 'Back','spice-starter-sites');?></a>
                        </form>
                    </div>
                    </div>
                        </div>
                    </div>
                    
                </div>

                <div align="center" id="myDiv" style="display:none;">
                    <div class="spice-starter-sites-importer-loader">
                        <img id="loading-image" src="<?php echo esc_url(SPICE_STARTER_SITES_PLUGIN_URL.'assets/images/import-new.gif');  ?>"  />
                        <p><?php esc_html_e( "Don't Refresh the Page . It may take a few minutes, Please Wait...","spice-starter-sites");?></p>
                    </div>
                </div>
                <div align="center" id="myDivs" style="display:none;">
                    <img src="<?php echo esc_url(SPICE_STARTER_SITES_PLUGIN_URL.'assets/images/completed.png');?>" >
                    <h3><?php esc_html_e('Success!','spice-starter-sites');?></h3>
                    <p><?php esc_html_e( 'The import process has been successful. Go to visit the site and Enjoy the theme.','spice-starter-sites');?></p>
                    <a href="<?php echo esc_url(site_url());?>" class="spice-starter-sites-importer-button button-primary" target="_blank"><?php esc_html_e( 'Visit Site','spice-starter-sites');?></a>
                </div>
            </div>
            
        </div>
        
        <script type="text/javascript">
            jQuery( document).ready( function( $ ){
                jQuery( '.spice_starter_sites_importer_demo_import').on( 'click', function( e ){
                    e.preventDefault();
                    var themedata=$(this).data('theme');
                   // alert(themedata);
                    var btn = $(this);
                    if ( btn.hasClass( 'disabled' ) ) {
                        return false;
                    }
                    $('.spice-starter-sites-importer-popup').addClass('is-visible');
                });
                    
                //return;
                jQuery( '.spice_starter_sites_importer_appprove').on( 'click', function( e ){
                    var themedata=jQuery('.spice_starter_sites_importer_demo_import').data('theme');
                    jQuery('.spice-starter-sites-importer-popup').removeClass('is-visible');
                    var params = {
                        'action': 'spice_starter_sites_importer_creater',
                        'themename':themedata,
                        '_nonce': '<?php echo esc_js(wp_create_nonce( 'spice_starter_sites_importer_demo_import' )); ?>',
                        _time:  new Date().getTime()
                    };

                    $.ajax({
                        type: 'POST',
                        url: window.ajaxurl, 
                        data: params,
                        beforeSend: function(result) {
                            $('#myDiv').show();
                        },
                        complete: function(result) {                            
                            $('#myDiv').hide();
                            $('#myDivs1').hide();
                            $('#myDivs').show();
                        },
                    });
                });            
          
                //no popup
                $('.spice_starter_sites_importer_cancel').on('click', function(event){
                    event.preventDefault();
                    $('.spice-starter-sites-importer-popup').removeClass('is-visible');
                });
                
                //close popup
                $('.spice-starter-sites-importer-popup').on('click', function(event){
                    if( $(event.target).is('.spice-starter-sites-importer-popup-close') || $(event.target).is('.spice-starter-sites-importer-popup') ) {
                        event.preventDefault();
                        $(this).removeClass('is-visible');
                    }
                });

                //close popup when clicking the esc keyboard button
                $(document).keyup(function(event){
                    if(event.which=='27'){
                        $('.spice-starter-sites-importer-popup').removeClass('is-visible');
                    }
                });
        } );
        </script>
        <div class="spice-starter-sites-importer-popup" role="alert">
            <div class="spice-starter-sites-importer-popup-container">
                <?php
                function spice_starter_sites_url_exists($url) {
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_NOBODY, true);
                    curl_exec($ch);
                    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                    if ($code == 200) {
                        $status = true;
                    } else {
                        $status = false;
                    }
                    curl_close($ch);
                    return $status;
                }
                function spice_starter_sites_search_array($id, $array) {
                   foreach ($array as $key => $val) {
                       if ($val['slug'] === $id) {
                           return $key;
                       }
                   }
                   return null;
                }

                $themename = isset($_GET['theme']) ? sanitize_text_field(wp_unslash($_GET['theme'])) : '';

                global $spice_starter_sites_importer_filepath,$spice_starter_sites_importer_pro_filepath;
                $checkfiles=array('content', 'customizer', 'widget');
                $findfile=false;
                 if(spice_starter_sites_search_array($themename, $spice_starter_sites_importer_pro_filepath)!=''){
                    for($i=0; $i< $length ; $i++){
                        $string='';
                        if(spice_starter_sites_url_exists($spice_starter_sites_importer_pro_filepath[$themename][$checkfiles[$i]])){
                            $findfile=true;
                         }
                         else{
                            // Escape $checkfiles[$i] using esc_html()
                            $escaped_file_name = esc_html($checkfiles[$i]);
                            $string .= '<p>' . $escaped_file_name . ' file not found</p>';
                            echo wp_kses_post($string); // Echo the string after building it
                         }
                     }
                 }
                 if(spice_starter_sites_search_array($themename, $spice_starter_sites_importer_filepath)!=null){
                    for($i=0; $i< $length ; $i++){
                        $string='';
                        if(spice_starter_sites_url_exists($spice_starter_sites_importer_filepath[$themename][$checkfiles[$i]])){
                            $findfile=true;                             
                        }
                        else{
                            // Escape $checkfiles[$i] using esc_html()
                            $escaped_file_name = esc_html($checkfiles[$i]);
                            $string .= '<p>' . $escaped_file_name . ' file not found</p>';
                            echo wp_kses_post($string); // Echo the string after building it
                        }
                    }
                 }
                 if($findfile==true){
                    ?>
                    <p><?php esc_html_e('Are you sure want to import demo content ?','spice-starter-sites');?></p>
                    <ul class="spice-starter-sites-importer-buttons">
                        <li><button class="spice-starter-sites-importer-button spice_starter_sites_importer_appprove button-primary " href="#"><?php esc_html_e('Yes','spice-starter-sites');?></button></li>
                        <li><button class="spice-starter-sites-importer-button spice_starter_sites_importer_cancel button-second" href="#"><?php esc_html_e('No','spice-starter-sites');?></button></li>
                    </ul>                        
                    <?php  
                 }
                ?>        
                <a href="#0" class="spice-starter-sites-importer-popup-close img-replace"></a>            
            </div>
        </div> 
        <?php
    }

}
add_action( 'wp_ajax_nopriv_spice_starter_sites_importer_creater', 'spice_starter_sites_importer_creater');
add_action( 'wp_ajax_spice_starter_sites_importer_creater', 'spice_starter_sites_importer_creater');


function spice_starter_sites_importer_creater(){

    global $wp_filesystem;
    
    // Initialize WP_Filesystem
    if ( !function_exists('WP_Filesystem') ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    $creds = request_filesystem_credentials(site_url());
    if ( !WP_Filesystem($creds) ) {
        // If initialization fails, exit the function
        wp_die( esc_html__('Unable to initialize WP_Filesystem.', 'spice-starter-sites'));
    }

    $theme_name = isset($_POST['themename']) ? sanitize_text_field(wp_unslash($_POST['themename'])) : '';     
    require_once SPICE_STARTER_SITES_PLUGIN_PATH . 'inc/importer/autoimporter.php';
    $theme=wp_get_theme();
    global $spice_starter_sites_importer_filepath,$spice_starter_sites_importer_pro_filepath;
    $uploads_dir = SPICE_STARTER_SITES_PLUGIN_PATH . 'inc/data/'.$theme_name;
    wp_mkdir_p( $uploads_dir );

    // Loop through files and use WP_Filesystem to write data
    foreach ($spice_starter_sites_importer_filepath as $spice_starter_sites_importer_target) {
        if ($theme_name === $spice_starter_sites_importer_target['slug']) {
            $uploadfiles = array('content', 'customizer', 'widget');
            foreach ($uploadfiles as $file) {
                $url = $spice_starter_sites_importer_target[$file];
                $path = $uploads_dir . '/' . basename($url);

                // Use cURL to get the file data
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $data = curl_exec($ch);
                curl_close($ch);

                // Write data to the file using WP_Filesystem
                if ( ! $wp_filesystem->put_contents($path, $data, FS_CHMOD_FILE) ) {
                    wp_die( esc_html__('Failed to write file.', 'spice-starter-sites'));
                }
            }
        }
    }

    foreach ($spice_starter_sites_importer_pro_filepath as $spice_starter_sites_importer_pro_target) {
        if ($theme_name === $spice_starter_sites_importer_pro_target['slug']) {
            $uploadfiles = array('content', 'customizer', 'widget');
            foreach ($uploadfiles as $file) {
                $url = $spice_starter_sites_importer_pro_target[$file];
                $path = $uploads_dir . '/' . basename($url);

                // Use cURL to get the file data
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $data = curl_exec($ch);
                curl_close($ch);

                // Write data to the file using WP_Filesystem
                if ( ! $wp_filesystem->put_contents($path, $data, FS_CHMOD_FILE) ) {
                    wp_die( esc_html__('Failed to write file.', 'spice-starter-sites'));
                }
            }
        }
    }        

    if ( ! class_exists( 'Spice_Starter_Sites_Importer_Auto' ) )
        die( 'Spice_Starter_Sites_Importer_Auto not found' );

    // call the function
    set_time_limit(1200);
    $autoimport = new Spice_Starter_Sites_Importer_Auto( );
    $args = array(
        'file'        => $uploads_dir. '/content.xml',
        'map_user_id' => 1
    );
    $autoimport->spice_starter_sites_importer_auto_callback($args);
    $autoimport->do_import(); 

    spice_starter_sites_importer_customizer_settings( $uploads_dir. '/customizer.dat');   

    spice_starter_sites_importer_process_import_file($uploads_dir. '/widget.wie' );
    
    do_action('spice_starter_sites_importer_after');
}

//After import
function spice_starter_sites_after_set_post(){
    update_option( 'show_on_front', 'posts' );
    update_option( 'page_on_front', 0 );
}
add_action('spice_starter_sites_importer_after','spice_starter_sites_after_set_post');