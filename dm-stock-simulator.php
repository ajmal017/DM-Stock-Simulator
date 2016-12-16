<?php if(!defined('ABSPATH')) die('Fatal Error');
/*
Plugin Name: Divest Media Stock Simulator
Plugin URI: http://divestmedia.com
Description: Divestmedia plugin for Stocks
Author: ralphjesy@gmail.com
Version: 1.0
Author URI: http://github.com/ralphjesy12
*/
define( 'DM_STOCKS_VERSION', '1.0' );
define( 'DM_STOCKS_MIN_WP_VERSION', '4.4' );
define( 'DM_STOCKS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DM_STOCKS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DM_STOCKS_DEBUG' , true );

define( 'DM_STOCKS_INITIAL_WALLET' , 100000);

// require_once DM_STOCKS_PLUGIN_DIR . '/vendor/autoload.php';
require_once( DM_STOCKS_PLUGIN_DIR . 'lib/class-dm-stocks-table.php');
require_once( DM_STOCKS_PLUGIN_DIR . 'lib/class-dm-stocks-users.php');
require_once( DM_STOCKS_PLUGIN_DIR . 'lib/class-dm-stocks.php');

if(class_exists('DMSTOCKS'))
{
    $DMSTOCKS = new DMSTOCKS();
    register_activation_hook(__FILE__, $DMSTOCKS->activate());
}
